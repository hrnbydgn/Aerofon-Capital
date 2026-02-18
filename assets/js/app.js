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
            <textarea class="bolum-icerik" placeholder="Bölüm içeriğini buraya yazın...">${escapeHtml(b.icerik || '')}</textarea>
            ${altListe}
        </div>`;
    }).join('');
    div.querySelectorAll('.bolum-sil').forEach(btn => {
        btn.addEventListener('click', () => bolumSil(btn.closest('.bolum-karti').dataset.id));
    });
    div.querySelectorAll('.alt-bolum-ekle').forEach(btn => {
        btn.addEventListener('click', () => altBolumEkle(btn.closest('.bolum-karti').dataset.id));
    });
    cocuklar.forEach(b => bolumleriCiz(bolumler, b.id));
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
            const icerik = k.querySelector('.bolum-icerik')?.value || '';
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
    document.getElementById('ayarBaslikFont').value = ayarlar.baslik_font_boyutu ?? 14;
    document.getElementById('ayarBaslikBuyuk').value = ayarlar.baslik_buyuk_harf !== false ? '1' : '0';
    document.getElementById('ayarSolKenar').value = ayarlar.sol_kenar ?? 4;
    document.getElementById('ayarSagKenar').value = ayarlar.sag_kenar ?? 2.5;
    document.getElementById('ayarUstKenar').value = ayarlar.ust_kenar ?? 2.5;
    document.getElementById('ayarAltKenar').value = ayarlar.alt_kenar ?? 2.5;
    document.getElementById('ayarGirinti').value = ayarlar.paragraf_girinti ?? 1;
}

function ayarlariFormdanOku() {
    return {
        font: document.getElementById('ayarFont').value,
        font_boyutu: parseInt(document.getElementById('ayarFontBoyutu').value) || 12,
        satir_araligi: parseFloat(document.getElementById('ayarSatirAraligi').value) || 1.5,
        baslik_font_boyutu: parseInt(document.getElementById('ayarBaslikFont').value) || 14,
        baslik_buyuk_harf: document.getElementById('ayarBaslikBuyuk').value === '1',
        sol_kenar: parseFloat(document.getElementById('ayarSolKenar').value) || 4,
        sag_kenar: parseFloat(document.getElementById('ayarSagKenar').value) || 2.5,
        ust_kenar: parseFloat(document.getElementById('ayarUstKenar').value) || 2.5,
        alt_kenar: parseFloat(document.getElementById('ayarAltKenar').value) || 2.5,
        paragraf_girinti: parseFloat(document.getElementById('ayarGirinti').value) || 1
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
        baslik_font_boyutu: 14,
        baslik_buyuk_harf: true,
        sol_kenar: 4,
        sag_kenar: 2.5,
        ust_kenar: 2.5,
        alt_kenar: 2.5,
        paragraf_girinti: 1
    });
}

function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}
