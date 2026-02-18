/**
 * Tez Yazım Arayüzü - Ana Uygulama
 */
const API = 'api.php';
let seciliProjeId = null;

document.addEventListener('DOMContentLoaded', () => {
    projeleriYukle();
    olaylariBagla();
});

function olaylariBagla() {
    document.getElementById('yeniProje').addEventListener('click', yeniProje);
    document.getElementById('ayarlarBtn').addEventListener('click', ayarlarAc);
    document.getElementById('ayarlarKaydet').addEventListener('click', ayarlarKaydet);
    document.getElementById('ayarlarVarsayilan').addEventListener('click', ayarlarVarsayilan);
    document.getElementById('wordIndir').addEventListener('click', wordIndir);
    document.getElementById('kaydetBtn').addEventListener('click', projeKaydet);
    document.getElementById('bolumEkle').addEventListener('click', () => bolumEkle(0));
    document.getElementById('ayarlarModal').addEventListener('click', (e) => {
        if (e.target.id === 'ayarlarModal') ayarlarKapat();
    });
    document.getElementById('tabloModal').addEventListener('click', (e) => {
        if (e.target.id === 'tabloModal') document.getElementById('tabloModal').classList.remove('aktif');
    });
    document.getElementById('tabloModalIptal').addEventListener('click', () => {
        document.getElementById('tabloModal').classList.remove('aktif');
    });
    document.querySelectorAll('.ayar-sekme').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.ayar-sekme').forEach(b => b.classList.remove('aktif'));
            document.querySelectorAll('.ayar-panel').forEach(p => p.classList.remove('aktif'));
            btn.classList.add('aktif');
            document.getElementById('panel-' + btn.dataset.sekme).classList.add('aktif');
        });
    });
}

async function projeleriYukle() {
    try {
        const r = await fetch(API);
        const projeler = await r.json();
        const ul = document.getElementById('projeListesi');
        ul.innerHTML = projeler.map(p => `
            <li data-id="${p.id}" class="${p.id === seciliProjeId ? 'aktif' : ''}">
                ${escapeHtml(p.baslik || 'İsimsiz')}
            </li>
        `).join('');
        ul.querySelectorAll('li').forEach(li => {
            li.addEventListener('click', () => projeSec(parseInt(li.dataset.id)));
        });
    } catch (e) {
        console.error(e);
    }
}

async function projeSec(id) {
    seciliProjeId = id;
    projeleriYukle();
    try {
        const r = await fetch(`${API}?action=proje&id=${id}`);
        const proje = await r.json();
        if (proje.hata) throw new Error(proje.hata);
        editoruDoldur(proje);
        document.getElementById('bosDurum').style.display = 'none';
        document.getElementById('tezEditor').style.display = 'block';
    } catch (e) {
        alert('Proje yüklenemedi: ' + e.message);
    }
}

function editoruDoldur(proje) {
    document.getElementById('tezBaslik').value = proje.baslik || '';
    document.getElementById('tezYazar').value = proje.yazar || '';
    document.getElementById('tezDanisman').value = proje.danisman || '';
    document.getElementById('tezUniversite').value = proje.universite || '';
    document.getElementById('tezEnstitu').value = proje.enstitu || '';
    document.getElementById('tezBolum').value = proje.bolum || '';
    document.getElementById('tezYil').value = proje.yil || new Date().getFullYear();
    bolumleriCizFlat(proje.bolumler || []);
    ayarlariFormaYaz(proje.ayarlar || {});
}

function parseIcerik(icerik) {
    if (!icerik || (typeof icerik === 'string' && icerik.trim() === '')) return [];
    if (typeof icerik === 'string' && icerik.trim().startsWith('[')) {
        try {
            const parsed = JSON.parse(icerik);
            if (Array.isArray(parsed)) return parsed.map(b => ({ ...b, id: b.id || blokId() }));
        } catch (_) {}
    }
    return [{ id: blokId(), type: 'text', content: typeof icerik === 'string' ? icerik : '' }];
}

function blokId() {
    return 'b' + Date.now() + '_' + Math.random().toString(36).slice(2, 8);
}

function bolumleriCiz(bolumler, parentId = 0) {
    const div = parentId === 0 ? document.getElementById('bolumListesi') : document.querySelector(`.bolum-karti[data-id="${parentId}"] .bolum-alt-listesi`);
    if (!div) return;
    const cocuklar = bolumler.filter(b => (b.parent_id || 0) == parentId).sort((a, b) => (a.sira || 0) - (b.sira || 0));
    div.innerHTML = cocuklar.map((b, i) => {
        const altListe = `<div class="bolum-alt-listesi"></div>`;
        return `
        <div class="bolum-karti bolum-seviye-${parentId ? 'alt' : 'ust'}" data-id="${b.id}" data-parent-id="${parentId}">
            <div class="bolum-karti-header">
                <input type="text" class="bolum-baslik" placeholder="Bölüm başlığı" value="${escapeHtml(b.baslik || '')}">
                <div class="bolum-aksiyonlar">
                    <button type="button" class="btn btn-outline btn-sm alt-bolum-ekle" title="Alt bölüm ekle">+ Alt</button>
                    <button type="button" class="btn btn-danger btn-sm bolum-sil">Sil</button>
                </div>
            </div>
            <div class="blok-editor" data-bolum-id="${b.id}">
                <div class="blok-listesi"></div>
                <div class="blok-ekle-grup">
                    <button type="button" class="btn btn-outline btn-sm blok-ekle" data-type="text">+ Metin</button>
                    <button type="button" class="btn btn-outline btn-sm blok-ekle" data-type="table">+ Tablo</button>
                    <button type="button" class="btn btn-outline btn-sm blok-ekle" data-type="image">+ Resim</button>
                </div>
            </div>
            ${altListe}
        </div>`;
    }).join('');
    div.querySelectorAll('.bolum-sil').forEach(btn => {
        btn.addEventListener('click', () => bolumSil(btn.closest('.bolum-karti').dataset.id));
    });
    div.querySelectorAll('.alt-bolum-ekle').forEach(btn => {
        btn.addEventListener('click', () => altBolumEkle(btn.closest('.bolum-karti').dataset.id));
    });
    cocuklar.forEach(b => {
        const editor = div.querySelector(`.bolum-karti[data-id="${b.id}"] .blok-editor`);
        if (editor) blokEditorInit(editor, parseIcerik(b.icerik));
    });
    cocuklar.forEach(b => bolumleriCiz(bolumler, b.id));
}

function blokEditorInit(container, bloklar) {
    const liste = container.querySelector('.blok-listesi');
    const render = () => {
        liste.innerHTML = bloklar.map((blok, idx) => blokRender(blok, idx)).join('');
        liste.querySelectorAll('.blok-sil').forEach(btn => {
            btn.addEventListener('click', () => {
                bloklar.splice(bloklar.indexOf(bloklar.find(b => b.id === btn.dataset.id)), 1);
                render();
            });
        });
        liste.querySelectorAll('.blok-yukari').forEach(btn => {
            btn.addEventListener('click', () => {
                const i = bloklar.findIndex(b => b.id === btn.dataset.id);
                if (i > 0) { [bloklar[i], bloklar[i - 1]] = [bloklar[i - 1], bloklar[i]]; render(); }
            });
        });
        liste.querySelectorAll('.blok-asagi').forEach(btn => {
            btn.addEventListener('click', () => {
                const i = bloklar.findIndex(b => b.id === btn.dataset.id);
                if (i < bloklar.length - 1) { [bloklar[i], bloklar[i + 1]] = [bloklar[i + 1], bloklar[i]]; render(); }
            });
        });
        liste.querySelectorAll('.blok-tablo-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const blok = bloklar.find(b => b.id === btn.dataset.id);
                if (blok && blok.type === 'table') tabloModalAc(blok, () => render());
            });
        });
        liste.querySelectorAll('.blok-resim-input').forEach(inp => {
            inp.addEventListener('change', async (e) => {
                const file = e.target.files[0];
                if (!file) return;
                const blok = bloklar.find(b => b.id === inp.dataset.id);
                if (!blok) return;
                const fd = new FormData();
                fd.append('file', file);
                try {
                    const r = await fetch('upload.php', { method: 'POST', body: fd });
                    const d = await r.json();
                    if (d.url) { blok.src = d.url; blok.caption = blok.caption || 'Şekil:'; render(); }
                    else alert(d.hata || 'Yükleme hatası');
                } catch (err) { alert('Yükleme hatası'); }
                inp.value = '';
            });
        });
        liste.querySelectorAll('.blok-textarea').forEach(ta => {
            const blok = bloklar.find(b => b.id === ta.dataset.id);
            if (blok && blok.content !== ta.value) blok.content = ta.value;
        });
        liste.querySelectorAll('.blok-resim-caption').forEach(inp => {
            const blok = bloklar.find(b => b.id === inp.dataset.id);
            if (blok) blok.caption = inp.value;
        });
    };
    container.querySelectorAll('.blok-ekle').forEach(btn => {
        btn.addEventListener('click', () => {
            const type = btn.dataset.type;
            if (type === 'text') bloklar.push({ id: blokId(), type: 'text', content: '' });
            else if (type === 'table') bloklar.push({ id: blokId(), type: 'table', caption: 'Tablo:', data: [['', ''], ['', '']], source: '' });
            else if (type === 'image') bloklar.push({ id: blokId(), type: 'image', src: '', caption: 'Şekil:', alt: '' });
            render();
        });
    });
    container.bloklar = bloklar;
    render();
}

function blokRender(blok, idx) {
    if (blok.type === 'text') {
        return `
        <div class="blok blok-text" data-id="${blok.id}">
            <div class="blok-toolbar">
                <span class="blok-tip">Metin</span>
                <div class="blok-actions">
                    <button type="button" class="blok-btn blok-yukari" data-id="${blok.id}" title="Yukarı">↑</button>
                    <button type="button" class="blok-btn blok-asagi" data-id="${blok.id}" title="Aşağı">↓</button>
                    <button type="button" class="blok-btn blok-sil" data-id="${blok.id}" title="Sil">×</button>
                </div>
            </div>
            <textarea class="blok-textarea" data-id="${blok.id}" placeholder="Metin yazın...">${escapeHtml(blok.content || '')}</textarea>
        </div>`;
    }
    if (blok.type === 'table') {
        const rows = (blok.data || [['', ''], ['', '']]).map(r => r.map(c => escapeHtml(c || '')).join('</td><td>')).join('</tr><tr><td>');
        return `
        <div class="blok blok-table" data-id="${blok.id}">
            <div class="blok-toolbar">
                <span class="blok-tip">Tablo</span>
                <div class="blok-actions">
                    <button type="button" class="blok-btn blok-tablo-edit" data-id="${blok.id}" title="Düzenle">✎</button>
                    <button type="button" class="blok-btn blok-yukari" data-id="${blok.id}">↑</button>
                    <button type="button" class="blok-btn blok-asagi" data-id="${blok.id}">↓</button>
                    <button type="button" class="blok-btn blok-sil" data-id="${blok.id}">×</button>
                </div>
            </div>
            <div class="blok-tablo-preview">
                <span class="tablo-caption">${escapeHtml(blok.caption || 'Tablo:')}</span>
                <table><tr><td>${rows || '<td></td><td></td>'}</tr></table>
            </div>
        </div>`;
    }
    if (blok.type === 'image') {
        const imgHtml = blok.src ? `<img src="${escapeHtml(blok.src)}" alt="${escapeHtml(blok.alt || '')}">` : '<div class="blok-resim-placeholder">Resim yükle</div>';
        return `
        <div class="blok blok-image" data-id="${blok.id}">
            <div class="blok-toolbar">
                <span class="blok-tip">Resim</span>
                <div class="blok-actions">
                    <label class="blok-btn blok-resim-yukle" title="Yükle" for="resim-${blok.id}">📷</label>
                    <input type="file" id="resim-${blok.id}" class="blok-resim-input" data-id="${blok.id}" accept="image/*" capture="environment" hidden>
                    <button type="button" class="blok-btn blok-yukari" data-id="${blok.id}">↑</button>
                    <button type="button" class="blok-btn blok-asagi" data-id="${blok.id}">↓</button>
                    <button type="button" class="blok-btn blok-sil" data-id="${blok.id}">×</button>
                </div>
            </div>
            <label class="blok-resim-wrap" for="resim-${blok.id}">
                ${imgHtml}
            </label>
            <input type="text" class="blok-resim-caption" data-id="${blok.id}" placeholder="Şekil açıklaması" value="${escapeHtml(blok.caption || '')}">
        </div>`;
    }
    return '';
}

function tabloModalAc(blok, onSave) {
    document.getElementById('tabloModal').classList.add('aktif');
    const tbody = document.getElementById('tabloModalTbody');
    const data = blok.data || [['', ''], ['', '']];
    document.getElementById('tabloModalCaption').value = blok.caption || 'Tablo:';
    document.getElementById('tabloModalSource').value = blok.source || '';
    tbody.innerHTML = data.map((row, ri) => `
        <tr data-row="${ri}">
            ${row.map((cell, ci) => `<td><input type="text" value="${escapeHtml(cell)}" data-row="${ri}" data-col="${ci}"></td>`).join('')}
            <td class="tablo-cell-actions">
                <button type="button" class="blok-btn tablo-row-del" data-row="${ri}">−</button>
            </td>
        </tr>
    `).join('');
    document.getElementById('tabloModalAddRow').onclick = () => {
        const firstRow = tbody.querySelector('tr');
        const cols = firstRow ? firstRow.querySelectorAll('td:not(.tablo-cell-actions)').length : 2;
        const cells = Array(cols).fill('<input type="text">').map(h => `<td>${h}</td>`).join('');
        const newRow = `<tr>${cells}<td class="tablo-cell-actions"><button type="button" class="blok-btn tablo-row-del">−</button></td></tr>`;
        tbody.insertAdjacentHTML('beforeend', newRow);
        tbody.querySelector('tr:last-child .tablo-row-del').addEventListener('click', function() {
            if (tbody.rows.length > 1) this.closest('tr').remove();
        });
    };
    tbody.querySelectorAll('.tablo-row-del').forEach(btn => {
        btn.addEventListener('click', function() { if (tbody.rows.length > 1) this.closest('tr').remove(); });
    });
    document.getElementById('tabloModalAddCol').onclick = () => {
        tbody.querySelectorAll('tr').forEach(tr => {
            const lastDataTd = tr.querySelector('td:not(.tablo-cell-actions):last-of-type');
            if (lastDataTd) {
                const newTd = document.createElement('td');
                newTd.innerHTML = '<input type="text">';
                tr.insertBefore(newTd, tr.querySelector('.tablo-cell-actions'));
            }
        });
    };
    document.getElementById('tabloModalKaydet').onclick = () => {
        blok.caption = document.getElementById('tabloModalCaption').value;
        blok.source = document.getElementById('tabloModalSource').value;
        blok.data = Array.from(tbody.querySelectorAll('tr')).map(tr => Array.from(tr.querySelectorAll('td:not(.tablo-cell-actions) input')).map(inp => inp.value));
        document.getElementById('tabloModal').classList.remove('aktif');
        onSave();
    };
}

function bolumleriCizFlat(bolumler) {
    document.getElementById('bolumListesi').innerHTML = '';
    bolumleriCiz(bolumler, 0);
}

async function yeniProje() {
    try {
        const r = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'proje_olustur',
                baslik: 'Yeni Tez',
                yil: new Date().getFullYear()
            })
        });
        const data = await r.json();
        if (data.hata) throw new Error(data.hata);
        await projeleriYukle();
        projeSec(data.id);
    } catch (e) {
        alert('Hata: ' + e.message);
    }
}

async function projeKaydet() {
    if (!seciliProjeId) return;
    const bolumler = bolumleriTopla();
    const ayarlar = ayarlariFormdanOku();
    try {
        const rProje = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'proje_guncelle',
                id: seciliProjeId,
                baslik: document.getElementById('tezBaslik').value,
                yazar: document.getElementById('tezYazar').value,
                danisman: document.getElementById('tezDanisman').value,
                universite: document.getElementById('tezUniversite').value,
                enstitu: document.getElementById('tezEnstitu').value,
                bolum: document.getElementById('tezBolum').value,
                yil: parseInt(document.getElementById('tezYil').value) || new Date().getFullYear(),
                ayarlar
            })
        });
        const dProje = await rProje.json();
        if (dProje.hata) throw new Error(dProje.hata);
        for (const b of bolumler) {
            if (b.id) {
                await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'bolum_guncelle',
                        id: b.id,
                        parent_id: b.parent_id || 0,
                        baslik: b.baslik,
                        icerik: b.icerik,
                        sira: b.sira
                    })
                });
            } else {
                const r = await fetch(API, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'bolum_ekle',
                        proje_id: seciliProjeId,
                        parent_id: b.parent_id || 0,
                        baslik: b.baslik,
                        icerik: b.icerik,
                        sira: b.sira
                    })
                });
                const data = await r.json();
                if (data.id) {
                    const karti = document.querySelector(`.bolum-karti[data-id=""]`);
                    if (karti) karti.dataset.id = data.id;
                }
            }
        }
        projeleriYukle();
        alert('Kaydedildi.');
    } catch (e) {
        alert('Kayıt hatası: ' + e.message);
    }
}

async function bolumEkle(parentId = 0) {
    if (!seciliProjeId) {
        alert('Önce bir proje seçin veya oluşturun.');
        return;
    }
    try {
        const r = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'bolum_ekle',
                proje_id: seciliProjeId,
                parent_id: parentId,
                baslik: 'Yeni Bölüm',
                icerik: ''
            })
        });
        const data = await r.json();
        if (data.hata) throw new Error(data.hata);
        const bolumler = bolumleriTopla();
        bolumler.push({ id: data.id, parent_id: parentId, baslik: 'Yeni Bölüm', icerik: '', sira: 999 });
        bolumleriCizFlat(bolumler);
    } catch (e) {
        alert('Hata: ' + e.message);
    }
}

async function altBolumEkle(parentId) {
    await bolumEkle(parseInt(parentId));
}

function bolumleriTopla() {
    const sonuc = [];
    function topla(container, parentId) {
        const kartlar = container.querySelectorAll(':scope > .bolum-karti');
        kartlar.forEach((k, i) => {
            const id = k.dataset.id ? parseInt(k.dataset.id) : null;
            const baslik = k.querySelector('.bolum-baslik')?.value || '';
            const editor = k.querySelector('.blok-editor');
            let icerik = '';
            if (editor && editor.bloklar) {
                editor.querySelectorAll('.blok-textarea').forEach(ta => {
                    const blok = editor.bloklar.find(b => b.id === ta.dataset.id);
                    if (blok) blok.content = ta.value;
                });
                editor.querySelectorAll('.blok-resim-caption').forEach(inp => {
                    const blok = editor.bloklar.find(b => b.id === inp.dataset.id);
                    if (blok) blok.caption = inp.value;
                });
                icerik = JSON.stringify(editor.bloklar);
            }
            sonuc.push({ id, parent_id: parentId, baslik, icerik, sira: i + 1 });
            const altListe = k.querySelector('.bolum-alt-listesi');
            if (altListe) topla(altListe, id);
        });
    }
    topla(document.getElementById('bolumListesi'), 0);
    return sonuc;
}

async function bolumSil(id) {
    if (!confirm('Bu bölümü silmek istediğinize emin misiniz?')) return;
    try {
        await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'bolum_sil', id: parseInt(id) })
        });
        document.querySelector(`.bolum-karti[data-id="${id}"]`)?.remove();
    } catch (e) {
        alert('Silme hatası: ' + e.message);
    }
}

async function wordIndir() {
    if (!seciliProjeId) return;
    try {
        await projeKaydet();
        window.location.href = `export.php?id=${seciliProjeId}`;
    } catch (e) {
        alert('Kayıt hatası: ' + e.message);
    }
}

function ayarlarAc() {
    document.getElementById('ayarlarModal').classList.add('aktif');
}

function ayarlarKapat() {
    document.getElementById('ayarlarModal').classList.remove('aktif');
}

function ayarlariFormaYaz(ayarlar) {
    document.getElementById('ayarFont').value = ayarlar.font || 'Times New Roman';
    document.getElementById('ayarFontBoyutu').value = ayarlar.font_boyutu ?? 12;
    document.getElementById('ayarSatirAraligi').value = ayarlar.satir_araligi ?? 1.5;
    document.getElementById('ayarBaslikFont').value = ayarlar.baslik_font_boyutu ?? 12;
    document.getElementById('ayarSolKenar').value = ayarlar.sol_kenar ?? 4;
    document.getElementById('ayarSagKenar').value = ayarlar.sag_kenar ?? 2.5;
    document.getElementById('ayarUstBolum').value = ayarlar.ust_bolum ?? 5;
    document.getElementById('ayarUstKenar').value = ayarlar.ust_kenar ?? 2.5;
    document.getElementById('ayarAltKenar').value = ayarlar.alt_kenar ?? 2.5;
    document.getElementById('ayarGirinti').value = ayarlar.paragraf_girinti ?? 1;
    document.getElementById('ayarTabloFont').value = ayarlar.tablo_font ?? 12;
    document.getElementById('ayarTabloSatir').value = ayarlar.tablo_satir ?? 1;
    document.getElementById('ayarTabloHizalama').value = ayarlar.tablo_hizalama ?? 'sol';
    document.getElementById('ayarTabloBaslikKalin').value = ayarlar.tablo_baslik_kalin !== false ? '1' : '0';
    document.getElementById('ayarTabloYaziFont').value = ayarlar.tablo_yazi_font ?? 12;
    document.getElementById('ayarTabloYaziKonum').value = ayarlar.tablo_yazi_konum ?? 'ust_sol';
    document.getElementById('ayarTabloYaziKalin').value = ayarlar.tablo_yazi_kalin !== false ? '1' : '0';
    document.getElementById('ayarTabloKenarlikKalin').value = ayarlar.tablo_kenarlik_kalin ?? 1;
    document.getElementById('ayarTabloKenarlikStil').value = ayarlar.tablo_kenarlik_stil ?? 'tek';
    document.getElementById('ayarTabloDisKenarlik').value = ayarlar.tablo_dis_kenarlik !== false ? '1' : '0';
    document.getElementById('ayarTabloIcKenarlik').value = ayarlar.tablo_ic_kenarlik !== false ? '1' : '0';
    document.getElementById('ayarTabloPaddingUst').value = ayarlar.tablo_padding_ust ?? 1.5;
    document.getElementById('ayarTabloPaddingAlt').value = ayarlar.tablo_padding_alt ?? 1.5;
    document.getElementById('ayarTabloPaddingSol').value = ayarlar.tablo_padding_sol ?? 2;
    document.getElementById('ayarTabloPaddingSag').value = ayarlar.tablo_padding_sag ?? 2;
    document.getElementById('ayarTabloUstBosluk').value = ayarlar.tablo_ust_bosluk ?? 0.5;
    document.getElementById('ayarTabloAltBosluk').value = ayarlar.tablo_alt_bosluk ?? 0.5;
    document.getElementById('ayarTabloGenislik').value = ayarlar.tablo_genislik ?? 100;
    document.getElementById('ayarTabloOrtala').value = ayarlar.tablo_ortala !== false ? '1' : '0';
    document.getElementById('ayarSekilFont').value = ayarlar.sekil_font ?? 12;
    document.getElementById('ayarSekilKonum').value = ayarlar.sekil_konum ?? 'alt_orta';
    document.getElementById('ayarSekilKalin').value = ayarlar.sekil_kalin !== false ? '1' : '0';
    document.getElementById('ayarKaynakFont').value = ayarlar.kaynak_font ?? 10;
}

function ayarlariFormdanOku() {
    return {
        font: document.getElementById('ayarFont').value,
        font_boyutu: parseInt(document.getElementById('ayarFontBoyutu').value) || 12,
        satir_araligi: parseFloat(document.getElementById('ayarSatirAraligi').value) || 1.5,
        baslik_font_boyutu: parseInt(document.getElementById('ayarBaslikFont').value) || 12,
        sol_kenar: parseFloat(document.getElementById('ayarSolKenar').value) || 4,
        sag_kenar: parseFloat(document.getElementById('ayarSagKenar').value) || 2.5,
        ust_bolum: parseFloat(document.getElementById('ayarUstBolum').value) || 5,
        ust_kenar: parseFloat(document.getElementById('ayarUstKenar').value) || 2.5,
        alt_kenar: parseFloat(document.getElementById('ayarAltKenar').value) || 2.5,
        paragraf_girinti: parseFloat(document.getElementById('ayarGirinti').value) || 1,
        tablo_font: parseInt(document.getElementById('ayarTabloFont').value) || 12,
        tablo_satir: parseFloat(document.getElementById('ayarTabloSatir').value) || 1,
        tablo_hizalama: document.getElementById('ayarTabloHizalama').value || 'sol',
        tablo_baslik_kalin: document.getElementById('ayarTabloBaslikKalin').value === '1',
        tablo_yazi_font: parseInt(document.getElementById('ayarTabloYaziFont').value) || 12,
        tablo_yazi_konum: document.getElementById('ayarTabloYaziKonum').value || 'ust_sol',
        tablo_yazi_kalin: document.getElementById('ayarTabloYaziKalin').value === '1',
        tablo_kenarlik_kalin: parseFloat(document.getElementById('ayarTabloKenarlikKalin').value) || 1,
        tablo_kenarlik_stil: document.getElementById('ayarTabloKenarlikStil').value || 'tek',
        tablo_dis_kenarlik: document.getElementById('ayarTabloDisKenarlik').value === '1',
        tablo_ic_kenarlik: document.getElementById('ayarTabloIcKenarlik').value === '1',
        tablo_padding_ust: parseFloat(document.getElementById('ayarTabloPaddingUst').value) || 1.5,
        tablo_padding_alt: parseFloat(document.getElementById('ayarTabloPaddingAlt').value) || 1.5,
        tablo_padding_sol: parseFloat(document.getElementById('ayarTabloPaddingSol').value) || 2,
        tablo_padding_sag: parseFloat(document.getElementById('ayarTabloPaddingSag').value) || 2,
        tablo_ust_bosluk: parseFloat(document.getElementById('ayarTabloUstBosluk').value) || 0.5,
        tablo_alt_bosluk: parseFloat(document.getElementById('ayarTabloAltBosluk').value) || 0.5,
        tablo_genislik: document.getElementById('ayarTabloGenislik').value === 'otomatik' ? 'otomatik' : (parseInt(document.getElementById('ayarTabloGenislik').value) || 100),
        tablo_ortala: document.getElementById('ayarTabloOrtala').value === '1',
        sekil_font: parseInt(document.getElementById('ayarSekilFont').value) || 12,
        sekil_konum: document.getElementById('ayarSekilKonum').value || 'alt_orta',
        sekil_kalin: document.getElementById('ayarSekilKalin').value === '1',
        kaynak_font: parseInt(document.getElementById('ayarKaynakFont').value) || 10
    };
}

function ayarlarKaydet() {
    const ayarlar = ayarlariFormdanOku();
    if (!seciliProjeId) {
        ayarlarKapat();
        return;
    }
    const proje = {
        baslik: document.getElementById('tezBaslik').value,
        yazar: document.getElementById('tezYazar').value,
        danisman: document.getElementById('tezDanisman').value,
        universite: document.getElementById('tezUniversite').value,
        enstitu: document.getElementById('tezEnstitu').value,
        bolum: document.getElementById('tezBolum').value,
        yil: parseInt(document.getElementById('tezYil').value) || new Date().getFullYear(),
        ayarlar
    };
    fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'proje_guncelle', id: seciliProjeId, ...proje })
    }).then(() => {
        ayarlarKapat();
        alert('Ayarlar kaydedildi.');
    }).catch(e => alert('Hata: ' + e.message));
}

function ayarlarVarsayilan() {
    ayarlariFormaYaz({
        font: 'Times New Roman',
        font_boyutu: 12,
        satir_araligi: 1.5,
        baslik_font_boyutu: 12,
        sol_kenar: 4,
        sag_kenar: 2.5,
        ust_bolum: 5,
        ust_kenar: 2.5,
        alt_kenar: 2.5,
        paragraf_girinti: 1,
        tablo_font: 12,
        tablo_satir: 1,
        tablo_hizalama: 'sol',
        tablo_baslik_kalin: true,
        tablo_yazi_font: 12,
        tablo_yazi_konum: 'ust_sol',
        tablo_yazi_kalin: true,
        tablo_kenarlik_kalin: 1,
        tablo_kenarlik_stil: 'tek',
        tablo_dis_kenarlik: true,
        tablo_ic_kenarlik: true,
        tablo_padding_ust: 1.5,
        tablo_padding_alt: 1.5,
        tablo_padding_sol: 2,
        tablo_padding_sag: 2,
        tablo_ust_bosluk: 0.5,
        tablo_alt_bosluk: 0.5,
        tablo_genislik: 100,
        tablo_ortala: true,
        sekil_font: 12,
        sekil_konum: 'alt_orta',
        sekil_kalin: true,
        kaynak_font: 10
    });
}

function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}
