document.addEventListener('DOMContentLoaded', () => {
    initNavigation();
    initAutoSave();
    initSettings();
});

function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const panels = document.querySelectorAll('.editor-panel');

    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const section = item.dataset.section;
            navItems.forEach(n => n.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            item.classList.add('active');
            document.getElementById(`panel-${section}`).classList.add('active');
        });
    });

    if (navItems.length) {
        navItems[0].click();
    }
}

function initAutoSave() {
    const editors = document.querySelectorAll('.tez-editor');
    editors.forEach(editor => {
        editor.addEventListener('blur', saveContent);
    });
}

function saveContent() {
    const data = {};
    document.querySelectorAll('.tez-editor').forEach(ed => {
        data[ed.name] = ed.value;
    });

    fetch('save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).catch(console.error);
}

function openSettings() {
    document.getElementById('settingsModal').classList.add('show');
}

function closeSettings() {
    document.getElementById('settingsModal').classList.remove('show');
}

function initSettings() {
    document.getElementById('settingsForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        fetch('save_config.php', {
            method: 'POST',
            body: formData
        }).then(() => {
            closeSettings();
            location.reload();
        });
    });
}

function exportWord() {
    const data = {};
    document.querySelectorAll('.tez-editor').forEach(ed => {
        data[ed.name] = ed.value;
    });

    fetch('save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    }).then(() => {
        window.location.href = 'export.php?t=' + Date.now();
    }).catch(console.error);
}
