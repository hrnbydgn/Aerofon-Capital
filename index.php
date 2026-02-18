<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tez Yazım Arayüzü</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app">
        <header class="header">
            <h1>📚 Tez Yazım Arayüzü</h1>
            <p class="subtitle">YÖK / Ulusal Yazım Kurallarına Uygun</p>
        </header>

        <main class="main">
            <aside class="sidebar">
                <div class="panel">
                    <h3>Projeler</h3>
                    <button type="button" class="btn btn-primary" id="yeniProje">+ Yeni Tez</button>
                    <ul class="proje-listesi" id="projeListesi"></ul>
                </div>
            </aside>

            <section class="icerik" id="icerikAlani">
                <div class="bos-durum" id="bosDurum">
                    <p>Yeni bir tez oluşturun veya soldan bir proje seçin.</p>
                </div>
                <div class="tez-editor" id="tezEditor" style="display:none">
                    <div class="editor-ust">
                        <div class="form-grup">
                            <label>Tez Başlığı</label>
                            <input type="text" id="tezBaslik" placeholder="Tez başlığını girin">
                        </div>
                        <div class="form-satir">
                            <div class="form-grup">
                                <label>Yazar</label>
                                <input type="text" id="tezYazar" placeholder="Ad Soyad">
                            </div>
                            <div class="form-grup">
                                <label>Danışman</label>
                                <input type="text" id="tezDanisman" placeholder="Danışman Adı">
                            </div>
                        </div>
                        <div class="form-satir">
                            <div class="form-grup">
                                <label>Üniversite</label>
                                <input type="text" id="tezUniversite" placeholder="Üniversite adı">
                            </div>
                            <div class="form-grup">
                                <label>Enstitü / Fakülte</label>
                                <input type="text" id="tezEnstitu" placeholder="Enstitü veya Fakülte">
                            </div>
                            <div class="form-grup">
                                <label>Bölüm / Anabilim Dalı</label>
                                <input type="text" id="tezBolum" placeholder="Bölüm adı">
                            </div>
                        </div>
                        <div class="form-grup">
                            <label>Yıl</label>
                            <input type="number" id="tezYil" min="1900" max="2100" placeholder="<?= date('Y') ?>">
                        </div>
                        <div class="editor-aksiyonlar">
                            <button type="button" class="btn btn-secondary" id="ayarlarBtn">⚙️ Yazım Ayarları</button>
                            <button type="button" class="btn btn-success" id="wordIndir">📥 RTF İndir</button>
                            <button type="button" class="btn btn-primary" id="kaydetBtn">💾 Kaydet</button>
                        </div>
                    </div>

                    <div class="bolumler">
                        <h3>Bölümler</h3>
                        <button type="button" class="btn btn-outline" id="bolumEkle">+ Bölüm Ekle</button>
                        <div class="bolum-listesi" id="bolumListesi"></div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Ayarlar Modal -->
    <div class="modal" id="ayarlarModal">
        <div class="modal-icerik">
            <h3>Yazım Ayarları (YÖK Varsayılan)</h3>
            <div class="ayar-grid">
                <div class="form-grup">
                    <label>Font</label>
                    <select id="ayarFont">
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Arial">Arial</option>
                        <option value="Calibri">Calibri</option>
                    </select>
                </div>
                <div class="form-grup">
                    <label>Font Boyutu (pt)</label>
                    <input type="number" id="ayarFontBoyutu" min="10" max="14" value="12">
                </div>
                <div class="form-grup">
                    <label>Satır Aralığı</label>
                    <select id="ayarSatirAraligi">
                        <option value="1">Tek</option>
                        <option value="1.15">1.15</option>
                        <option value="1.5" selected>1.5 (YÖK)</option>
                        <option value="2">Çift</option>
                    </select>
                </div>
                <div class="form-grup">
                    <label>Başlık Font Boyutu (pt)</label>
                    <input type="number" id="ayarBaslikFont" min="12" max="18" value="14">
                </div>
                <div class="form-grup">
                    <label>Başlıkları Büyük Harf</label>
                    <select id="ayarBaslikBuyuk">
                        <option value="1" selected>Evet</option>
                        <option value="0">Hayır</option>
                    </select>
                </div>
                <div class="form-grup">
                    <label>Sol Kenar (cm)</label>
                    <input type="number" id="ayarSolKenar" min="2" max="6" step="0.5" value="4">
                </div>
                <div class="form-grup">
                    <label>Sağ Kenar (cm)</label>
                    <input type="number" id="ayarSagKenar" min="1" max="4" step="0.5" value="2.5">
                </div>
                <div class="form-grup">
                    <label>Üst/Alt Kenar (cm)</label>
                    <input type="number" id="ayarUstKenar" min="1" max="4" step="0.5" value="2.5">
                </div>
                <div class="form-grup">
                    <label>Paragraf Girinti (cm)</label>
                    <input type="number" id="ayarGirinti" min="0.5" max="2" step="0.25" value="1.25">
                </div>
                <div class="form-grup">
                    <label>Alt Kenar (cm)</label>
                    <input type="number" id="ayarAltKenar" min="1" max="4" step="0.5" value="2.5">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="ayarlarVarsayilan">Varsayılana Dön</button>
                <button type="button" class="btn btn-primary" id="ayarlarKaydet">Kaydet</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
