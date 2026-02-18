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
                <h4>Metin</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Metin Fontu</label>
                        <select id="ayarFont">
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Arial">Arial</option>
                            <option value="Calibri">Calibri</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Garamond">Garamond</option>
                            <option value="Palatino Linotype">Palatino Linotype</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Metin Boyutu (pt)</label>
                        <input type="number" id="ayarFontBoyutu" min="9" max="14" value="12">
                    </div>
                    <div class="form-grup">
                        <label>Satır Aralığı</label>
                        <select id="ayarSatirAraligi">
                            <option value="1">Tek</option>
                            <option value="1.2">1.2</option>
                            <option value="1.25">1.25</option>
                            <option value="1.15">1.15</option>
                            <option value="1.5" selected>1.5 (YÖK)</option>
                            <option value="2">Çift</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Paragraf Girinti (cm)</label>
                        <input type="number" id="ayarGirinti" min="0" max="2" step="0.25" value="1">
                    </div>
                    <div class="form-grup">
                        <label>Paragraf Öncesi Boşluk (pt)</label>
                        <input type="number" id="ayarParagrafOncesi" min="0" max="24" value="0">
                    </div>
                    <div class="form-grup">
                        <label>Paragraf Sonrası Boşluk (pt)</label>
                        <input type="number" id="ayarParagrafSonrasi" min="0" max="24" value="6">
                    </div>
                </div>
                <h4>Başlıklar</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>1. Derece Font (pt)</label>
                        <input type="number" id="ayarBaslik1Font" min="10" max="18" value="12">
                    </div>
                    <div class="form-grup">
                        <label>1. Derece Büyük Harf</label>
                        <select id="ayarBaslik1Buyuk">
                            <option value="1" selected>Evet</option>
                            <option value="0">Hayır</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>2. Derece Font (pt)</label>
                        <input type="number" id="ayarBaslik2Font" min="10" max="16" value="12">
                    </div>
                    <div class="form-grup">
                        <label>3. Derece Font (pt)</label>
                        <input type="number" id="ayarBaslik3Font" min="10" max="14" value="12">
                    </div>
                </div>
                <h4>Önizleme</h4>
                <div class="ayar-onizleme" id="sayfaOnizleme">
                    <div class="onizleme-baslik1">1. GİRİŞ</div>
                    <div class="onizleme-baslik2">1.1. Alt Başlık Örneği</div>
                    <div class="onizleme-paragraf">Bu bir paragraf örneğidir. Tez metninde kullanılacak font, satır aralığı ve girinti ayarları burada görüntülenir.</div>
                </div>
            </div>

            <div class="ayar-panel" id="panel-kenar">
                <h4>Kenar Boşlukları (cm)</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Sol Kenar</label>
                        <input type="number" id="ayarSolKenar" min="2" max="6" step="0.5" value="4">
                    </div>
                    <div class="form-grup">
                        <label>Sağ Kenar</label>
                        <input type="number" id="ayarSagKenar" min="1" max="4" step="0.5" value="2.5">
                    </div>
                    <div class="form-grup">
                        <label>Bölüm Başında Üst</label>
                        <input type="number" id="ayarUstBolum" min="2" max="8" step="0.5" value="5">
                    </div>
                    <div class="form-grup">
                        <label>Diğer Sayfalarda Üst</label>
                        <input type="number" id="ayarUstKenar" min="1" max="4" step="0.5" value="2.5">
                    </div>
                    <div class="form-grup">
                        <label>Alt Kenar</label>
                        <input type="number" id="ayarAltKenar" min="1" max="4" step="0.5" value="2.5">
                    </div>
                    <div class="form-grup">
                        <label>Cilt Payı (sol ek)</label>
                        <input type="number" id="ayarCiltPayi" min="0" max="2" step="0.25" value="0">
                    </div>
                </div>
                <h4>Önizleme</h4>
                <div class="ayar-onizleme ayar-kenar-onizleme" id="kenarOnizleme">
                    <div class="kenar-onizleme-sayfa">
                        <span class="kenar-sol">Sol: <span id="kenarSolVal">4</span> cm</span>
                        <span class="kenar-sag">Sağ: <span id="kenarSagVal">2.5</span> cm</span>
                        <span class="kenar-ust">Üst: <span id="kenarUstVal">2.5</span> cm</span>
                        <span class="kenar-alt">Alt: <span id="kenarAltVal">2.5</span> cm</span>
                        <span class="kenar-bolum">Bölüm üst: <span id="kenarBolumVal">5</span> cm</span>
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
                <h4>Önizleme</h4>
                <div class="ayar-onizleme ayar-tablo-onizleme" id="tabloOnizleme">
                    <div class="tablo-onizleme-caption">Tablo 1.1: Örnek Tablo Başlığı</div>
                    <table class="tablo-onizleme-tablo">
                        <tr><th>Başlık 1</th><th>Başlık 2</th></tr>
                        <tr><td>Hücre 1</td><td>Hücre 2</td></tr>
                        <tr><td>Veri A</td><td>Veri B</td></tr>
                    </table>
                    <div class="tablo-onizleme-kaynak">Kaynak: Örnek kaynak (2024)</div>
                </div>
            </div>

            <div class="ayar-panel" id="panel-sekil">
                <h4>Şekil Yazısı</h4>
                <div class="ayar-grid">
                    <div class="form-grup">
                        <label>Şekil Font (pt)</label>
                        <input type="number" id="ayarSekilFont" min="8" max="14" value="12">
                    </div>
                    <div class="form-grup">
                        <label>Konum</label>
                        <select id="ayarSekilKonum">
                            <option value="alt_orta" selected>Altta, ortada</option>
                            <option value="alt_sol">Altta, sola yaslı</option>
                            <option value="ust_orta">Üstte, ortada</option>
                            <option value="ust_sol">Üstte, sola yaslı</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Kalın</label>
                        <select id="ayarSekilKalin">
                            <option value="1" selected>Evet</option>
                            <option value="0">Hayır</option>
                        </select>
                    </div>
                    <div class="form-grup">
                        <label>Kaynak Font (pt)</label>
                        <input type="number" id="ayarKaynakFont" min="8" max="12" value="10">
                    </div>
                    <div class="form-grup">
                        <label>Şekil Üst Boşluk (pt)</label>
                        <input type="number" id="ayarSekilUstBosluk" min="0" max="24" value="6">
                    </div>
                    <div class="form-grup">
                        <label>Şekil Alt Boşluk (pt)</label>
                        <input type="number" id="ayarSekilAltBosluk" min="0" max="24" value="6">
                    </div>
                </div>
                <h4>Önizleme</h4>
                <div class="ayar-onizleme ayar-sekil-onizleme" id="sekilOnizleme">
                    <div class="sekil-onizleme-placeholder">[Şekil alanı]</div>
                    <div class="sekil-onizleme-caption">Şekil 1.1: Örnek şekil açıklaması</div>
                    <div class="sekil-onizleme-kaynak">Kaynak: Örnek kaynak</div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="ayarlarVarsayilan">Varsayılana Dön</button>
                <button type="button" class="btn btn-primary" id="ayarlarKaydet">Kaydet</button>
            </div>
        </div>
    </div>

    <!-- Tablo Düzenleme Modal -->
    <div class="modal" id="tabloModal">
        <div class="modal-icerik">
            <h3>Tablo Düzenle</h3>
            <div class="form-grup">
                <label>Tablo Başlığı</label>
                <input type="text" id="tabloModalCaption" placeholder="Tablo 1.1:">
            </div>
            <div class="tablo-modal-tbody-wrap">
                <table class="tablo-modal-tablo">
                    <tbody id="tabloModalTbody"></tbody>
                </table>
            </div>
            <div class="tablo-modal-actions">
                <button type="button" class="btn btn-outline btn-sm" id="tabloModalAddRow">+ Satır</button>
                <button type="button" class="btn btn-outline btn-sm" id="tabloModalAddCol">+ Sütun</button>
            </div>
            <div class="form-grup">
                <label>Kaynak</label>
                <input type="text" id="tabloModalSource" placeholder="Kaynak: ...">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="tabloModalIptal">İptal</button>
                <button type="button" class="btn btn-primary" id="tabloModalKaydet">Kaydet</button>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
