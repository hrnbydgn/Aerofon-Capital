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
    document.getElementById('bolumEkle').addEventListener('click', bolumEkle);
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
    bolumleriCiz(proje.bolumler || []);
    ayarlariFormaYaz(proje.ayarlar || {});
}

function bolumleriCiz(bolumler) {
    const div = document.getElementById('bolumListesi');
    div.innerHTML = bolumler.map((b, i) => `
        <div class="bolum-karti" data-id="${b.id}">
            <div class="bolum-karti-header">
                <input type="text" class="bolum-baslik" placeholder="Bölüm başlığı" value="${escapeHtml(b.baslik || '')}">
                <button type="button" class="btn btn-danger btn-sm bolum-sil">Sil</button>
            </div>
            <textarea class="bolum-icerik" placeholder="Bölüm içeriğini buraya yazın...">${escapeHtml(b.icerik || '')}</textarea>
        </div>
    `).join('');
    div.querySelectorAll('.bolum-sil').forEach(btn => {
        btn.addEventListener('click', () => bolumSil(btn.closest('.bolum-karti').dataset.id));
    });
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
    const bolumler = [];
    document.querySelectorAll('.bolum-karti').forEach((k, i) => {
        const id = k.dataset.id;
        const baslik = k.querySelector('.bolum-baslik').value;
        const icerik = k.querySelector('.bolum-icerik').value;
        bolumler.push({ id: id ? parseInt(id) : undefined, sira: i + 1, baslik, icerik });
    });
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
                        baslik: b.baslik,
                        icerik: b.icerik,
                        sira: b.sira
                    })
                });
                const data = await r.json();
                if (data.id) {
                    const karti = document.querySelector(`.bolum-karti:not([data-id])`);
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

async function bolumEkle() {
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
                baslik: 'Yeni Bölüm',
                icerik: ''
            })
        });
        const data = await r.json();
        if (data.hata) throw new Error(data.hata);
        const bolumler = [];
        document.querySelectorAll('.bolum-karti').forEach((k, i) => {
            bolumler.push({
                id: k.dataset.id ? parseInt(k.dataset.id) : null,
                baslik: k.querySelector('.bolum-baslik').value,
                icerik: k.querySelector('.bolum-icerik').value
            });
        });
        bolumler.push({ id: data.id, baslik: 'Yeni Bölüm', icerik: '' });
        bolumleriCiz(bolumler);
    } catch (e) {
        alert('Hata: ' + e.message);
    }
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
    document.getElementById('ayarGirinti').value = ayarlar.paragraf_girinti ?? 1.25;
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
        paragraf_girinti: parseFloat(document.getElementById('ayarGirinti').value) || 1.25
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
        paragraf_girinti: 1.25
    });
}

function escapeHtml(s) {
    const div = document.createElement('div');
    div.textContent = s;
    return div.innerHTML;
}
