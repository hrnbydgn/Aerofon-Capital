import flet as ft


def main(page: ft.Page):
    # Sayfa ayarları
    page.title = "Yapılacaklar Listesi"
    page.theme_mode = ft.ThemeMode.LIGHT
    page.padding = 20
    page.scroll = ft.ScrollMode.AUTO

    # Mobil uyumlu genişlik
    page.window.width = 400
    page.window.height = 700

    # Tema renkleri
    page.theme = ft.Theme(
        color_scheme_seed=ft.Colors.DEEP_PURPLE,
    )

    # Görev listesi
    tasks_column = ft.Column(spacing=8)

    # Görev ekleme fonksiyonu
    def add_task(e):
        task_text = task_input.value.strip()
        if not task_text:
            task_input.error_text = "Lütfen bir görev girin"
            page.update()
            return

        task_input.error_text = None

        # Görev satırı oluştur
        task_row = ft.Container(
            content=ft.Row(
                controls=[
                    ft.Checkbox(
                        label=task_text,
                        on_change=lambda e: toggle_task(e),
                        expand=True,
                    ),
                    ft.IconButton(
                        icon=ft.Icons.DELETE_OUTLINE,
                        icon_color=ft.Colors.RED_400,
                        tooltip="Sil",
                        on_click=lambda e, row=None: delete_task(e),
                    ),
                ],
                alignment=ft.MainAxisAlignment.SPACE_BETWEEN,
            ),
            bgcolor=ft.Colors.WHITE,
            border_radius=12,
            padding=ft.padding.symmetric(horizontal=12, vertical=4),
            shadow=ft.BoxShadow(
                spread_radius=0,
                blur_radius=4,
                color=ft.Colors.with_opacity(0.1, ft.Colors.BLACK),
                offset=ft.Offset(0, 2),
            ),
        )

        # Silme butonuna referans ver
        task_row.content.controls[1].on_click = lambda e, r=task_row: delete_task(e, r)

        tasks_column.controls.append(task_row)
        task_input.value = ""
        update_counter()
        page.update()

    # Görev silme
    def delete_task(e, row):
        tasks_column.controls.remove(row)
        update_counter()
        page.update()

    # Görev tamamlama
    def toggle_task(e):
        cb = e.control
        if cb.value:
            cb.label = f"✅ {cb.label}" if not cb.label.startswith("✅") else cb.label
        else:
            cb.label = cb.label.replace("✅ ", "")
        page.update()

    # Tüm görevleri temizle
    def clear_all(e):
        tasks_column.controls.clear()
        update_counter()
        page.update()

    # Sayaç güncelle
    def update_counter():
        counter_text.value = f"Toplam: {len(tasks_column.controls)} görev"

    # Sayaç
    counter_text = ft.Text(
        value="Toplam: 0 görev",
        size=14,
        color=ft.Colors.GREY_600,
    )

    # Görev giriş alanı
    task_input = ft.TextField(
        hint_text="Yeni görev ekle...",
        expand=True,
        border_radius=12,
        on_submit=add_task,
        autofocus=True,
    )

    # Başlık
    header = ft.Container(
        content=ft.Column(
            controls=[
                ft.Text(
                    "📋 Yapılacaklar",
                    size=28,
                    weight=ft.FontWeight.BOLD,
                    color=ft.Colors.DEEP_PURPLE,
                ),
                ft.Text(
                    "Görevlerini kolayca yönet",
                    size=14,
                    color=ft.Colors.GREY_500,
                ),
            ],
            spacing=4,
        ),
        padding=ft.padding.only(bottom=16),
    )

    # Giriş satırı
    input_row = ft.Row(
        controls=[
            task_input,
            ft.FloatingActionButton(
                icon=ft.Icons.ADD,
                bgcolor=ft.Colors.DEEP_PURPLE,
                on_click=add_task,
                mini=True,
            ),
        ],
        spacing=8,
    )

    # Alt bilgi satırı
    footer_row = ft.Row(
        controls=[
            counter_text,
            ft.TextButton(
                text="Tümünü Temizle",
                icon=ft.Icons.DELETE_SWEEP,
                on_click=clear_all,
                style=ft.ButtonStyle(color=ft.Colors.RED_400),
            ),
        ],
        alignment=ft.MainAxisAlignment.SPACE_BETWEEN,
    )

    # Sayfa düzeni
    page.add(
        header,
        input_row,
        ft.Divider(height=16, color=ft.Colors.TRANSPARENT),
        footer_row,
        ft.Divider(height=8, color=ft.Colors.TRANSPARENT),
        tasks_column,
    )


# Uygulama başlat
ft.app(target=main)
