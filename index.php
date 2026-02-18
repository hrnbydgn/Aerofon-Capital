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
        <div class="modal-icerik modal-icerik-genis">
            <h3>Yazım Ayarları</h3>
            <div class="ayar-sekmeler">
                <button type="button" class="ayar-sekme aktif" data-sekme="sayfa">Sayfa & Metin</button>
                <button type="button" class="ayar-sekme" data-sekme="kenar">Kenar Boşlukları</button>
                <button type="button" class="ayar-sekme" data-sekme="tablo">Tablo</button>
                <button type="button" class="ayar-sekme" data-sekme="sekil">Şekil</button>
            </div>

            <div class="ayar-panel aktif" id="panel-sayfa">
                <h4>Sayfa & Metin</h4>
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
                        <label>Başlık Font (pt)</label>
                        <input type="number" id="ayarBaslikFont" min="10" max="14" value="12">
                    </div>
                    <div class="form-grup">
                        <label>Paragraf Girinti (cm)</label>
                        <input type="number" id="ayarGirinti" min="0.5" max="2" step="0.25" value="1">
                    </div>
                </div>
            </div>

            <div class="ayar-panel" id="panel-kenar">
                <h4>Kenar Boşlukları</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Sol Kenar (cm)</label>
                        <input type="number" id="ayarSolKenar" min="2" max="6" step="0.5" value="4">
                    </div>
                    <div class="form-grup">
                        <label>Sağ Kenar (cm)</label>
                        <input type="number" id="ayarSagKenar" min="1" max="4" step="0.5" value="2.5">
                    </div>
                    <div class="form-grup">
                        <label>Bölüm Başında Üst (cm)</label>
                        <input type="number" id="ayarUstBolum" min="2" max="8" step="0.5" value="5" title="Ana bölüm sayfası üst boşluk">
                    </div>
                    <div class="form-grup">
                        <label>Diğer Sayfalarda Üst (cm)</label>
                        <input type="number" id="ayarUstKenar" min="1" max="4" step="0.5" value="2.5">
                    </div>
                    <div class="form-grup">
                        <label>Alt Kenar (cm)</label>
                        <input type="number" id="ayarAltKenar" min="1" max="4" step="0.5" value="2.5">
                    </div>
                </div>
            </div>

            <div class="ayar-panel" id="panel-tablo">
                <h4>Tablo Yazısı (Başlık)</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Tablo Yazısı Font (pt)</label>
                        <input type="number" id="ayarTabloYaziFont" min="8" max="14" value="12">
                    </div>
                    <div class="form-grup">
                        <label>Tablo Yazısı Konumu</label>
                        <select id="ayarTabloYaziKonum">
                            <option value="ust_sol" selected>Üstte, sola yaslı</option>
                            <option value="ust_orta">Üstte, ortada</option>
                            <option value="alt_sol">Altta, sola yaslı</option>
                            <option value="alt_orta">Altta, ortada</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Tablo Yazısı Kalın</label>
                        <select id="ayarTabloYaziKalin">
                            <option value="1" selected>Evet</option>
                            <option value="0">Hayır</option>
                        </select>
                    </div>
                </div>
                <h4>Tablo İçeriği</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Tablo Font (pt)</label>
                        <input type="number" id="ayarTabloFont" min="8" max="14" value="12">
                    </div>
                    <div class="form-grup">
                        <label>Satır Aralığı</label>
                        <select id="ayarTabloSatir">
                            <option value="1" selected>Tek</option>
                            <option value="1.15">1.15</option>
                            <option value="1.5">1.5</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Metin Hizalama</label>
                        <select id="ayarTabloHizalama">
                            <option value="sol" selected>Sola yaslı</option>
                            <option value="orta">Ortada</option>
                            <option value="sag">Sağa yaslı</option>
                            <option value="iki_yana">İki yana yaslı</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Başlık Satırı Kalın</label>
                        <select id="ayarTabloBaslikKalin">
                            <option value="1" selected>Evet</option>
                            <option value="0">Hayır</option>
                        </select>
                    </div>
                </div>
                <h4>Kenarlıklar</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Kenarlık Kalınlığı (pt)</label>
                        <select id="ayarTabloKenarlikKalin">
                            <option value="0.5">0.5</option>
                            <option value="1" selected>1</option>
                            <option value="1.5">1.5</option>
                            <option value="2">2</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Kenarlık Stili</label>
                        <select id="ayarTabloKenarlikStil">
                            <option value="tek" selected>Tek çizgi</option>
                            <option value="cift">Çift çizgi</option>
                            <option value="noktali">Noktalı</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Dış Kenarlık</label>
                        <select id="ayarTabloDisKenarlik">
                            <option value="1" selected>Var</option>
                            <option value="0">Yok</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>İç Kenarlık (Hücreler)</label>
                        <select id="ayarTabloIcKenarlik">
                            <option value="1" selected>Var</option>
                            <option value="0">Yok</option>
                        </select>
                    </div>
                </div>
                <h4>Boşluklar (mm)</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Hücre Üst Boşluk</label>
                        <input type="number" id="ayarTabloPaddingUst" min="0" max="10" step="0.5" value="1.5">
                    </div>
                    <div class="form-grup">
                        <label>Hücre Alt Boşluk</label>
                        <input type="number" id="ayarTabloPaddingAlt" min="0" max="10" step="0.5" value="1.5">
                    </div>
                    <div class="form-grup">
                        <label>Hücre Sol Boşluk</label>
                        <input type="number" id="ayarTabloPaddingSol" min="0" max="10" step="0.5" value="2">
                    </div>
                    <div class="form-grup">
                        <label>Hücre Sağ Boşluk</label>
                        <input type="number" id="ayarTabloPaddingSag" min="0" max="10" step="0.5" value="2">
                    </div>
                    <div class="form-grup">
                        <label>Tablo Üst Boşluk (cm)</label>
                        <input type="number" id="ayarTabloUstBosluk" min="0" max="5" step="0.5" value="0.5">
                    </div>
                    <div class="form-grup">
                        <label>Tablo Alt Boşluk (cm)</label>
                        <input type="number" id="ayarTabloAltBosluk" min="0" max="5" step="0.5" value="0.5">
                    </div>
                </div>
                <h4>Genel</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Tablo Genişliği</label>
                        <select id="ayarTabloGenislik">
                            <option value="100" selected>Sayfa genişliği (%100)</option>
                            <option value="90">%90</option>
                            <option value="80">%80</option>
                            <option value="otomatik">İçeriğe göre</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Tablo Ortala</label>
                        <select id="ayarTabloOrtala">
                            <option value="1" selected>Evet</option>
                            <option value="0">Hayır</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="ayar-panel" id="panel-sekil">
                <h4>Şekil Yazısı</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Şekil Yazısı Font (pt)</label>
                        <input type="number" id="ayarSekilFont" min="8" max="14" value="12">
                    </div>
                    <div class="form-grup">
                        <label>Şekil Yazısı Konumu</label>
                        <select id="ayarSekilKonum">
                            <option value="alt_orta" selected>Altta, ortada</option>
                            <option value="alt_sol">Altta, sola yaslı</option>
                            <option value="ust_orta">Üstte, ortada</option>
                            <option value="ust_sol">Üstte, sola yaslı</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Şekil Yazısı Kalın</label>
                        <select id="ayarSekilKalin">
                            <option value="1" selected>Evet</option>
                            <option value="0">Hayır</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Kaynak Font (pt)</label>
                        <input type="number" id="ayarKaynakFont" min="8" max="12" value="10" title="Tablo/şekil kaynağı">
                    </div>
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
