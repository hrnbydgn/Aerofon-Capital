(() => {
  const mockCpData = {
    'CP-01': { name: 'CP-01', active: 'Enabled', direction: 'North', obstacle: true },
    'CP-02': { name: 'CP-02', active: 'Disabled', direction: 'East', obstacle: false },
    'CP-03': { name: 'CP-03', active: 'Enabled', direction: 'South', obstacle: true }
  };

  const getBootstrapComponent = (element, Constructor) => {
    if (!element || typeof bootstrap === 'undefined') return null;
    return bootstrap.getOrCreateInstance(element, Constructor);
  };

  const selectConnectionPoint = (cpId) => {
    const data = mockCpData[cpId] ?? {
      name: cpId,
      active: 'Enabled',
      direction: 'North',
      obstacle: false
    };

    updateDesktopDetailForm(data);
    updateMobileDetailForm(data);

    console.info(`[Mock] Selected connection point: ${cpId}`, data);
  };

  const updateDesktopDetailForm = ({ name, active, direction, obstacle }) => {
    const nameInput = document.getElementById('cpName');
    const activeSelect = document.getElementById('cpActive');
    const directionInput = document.getElementById('cpDirection');
    const obstacleCheck = document.getElementById('cpObstacle');

    if (!nameInput || !activeSelect || !directionInput || !obstacleCheck) return;

    nameInput.value = name ?? '';
    activeSelect.value = active ?? 'Enabled';
    directionInput.value = direction ?? '';
    obstacleCheck.checked = Boolean(obstacle);
  };

  const updateMobileDetailForm = ({ name, active, direction, obstacle }) => {
    const nameInput = document.getElementById('mobileCpName');
    const activeSelect = document.getElementById('mobileCpActive');
    const directionInput = document.getElementById('mobileCpDirection');
    const obstacleCheck = document.getElementById('mobileCpObstacle');

    if (!nameInput || !activeSelect || !directionInput || !obstacleCheck) return;

    nameInput.value = name ?? '';
    activeSelect.value = active ?? 'Enabled';
    directionInput.value = direction ?? '';
    obstacleCheck.checked = Boolean(obstacle);
  };

  const handleSaveClick = (event) => {
    event.preventDefault();
    const payload = {
      name: document.getElementById('cpName')?.value ?? '',
      active: document.getElementById('cpActive')?.value ?? 'Enabled',
      direction: document.getElementById('cpDirection')?.value ?? '',
      obstacle: document.getElementById('cpObstacle')?.checked ?? false
    };

    console.info('[Mock] Saving CP detail', payload);
  };

  const handleCpClick = (event) => {
    const target = event.currentTarget;
    const cpId = target?.dataset?.cpId;
    if (!cpId) return;

    selectConnectionPoint(cpId);

    if (window.innerWidth < 768) {
      mobilePanel?.show();
    }
  };

  const bindCpButtons = () => {
    const cpButtons = document.querySelectorAll('[data-cp-id]');
    cpButtons.forEach((btn) => {
      btn.addEventListener('click', handleCpClick);
    });
  };

  let fpvModal;
  let mobilePanel;

  const initOverlays = () => {
    const fpvModalElement = document.getElementById('fpvModal');
    const mobilePanelElement = document.getElementById('mobileCpPanel');

    if (fpvModalElement) {
      fpvModal = getBootstrapComponent(fpvModalElement, bootstrap.Modal);
    }

    if (mobilePanelElement) {
      mobilePanel = getBootstrapComponent(mobilePanelElement, bootstrap.Offcanvas);
    }
  };

  const bindOverlayTriggers = () => {
    const fpvTrigger = document.getElementById('openFpvModal');
    const panelTrigger = document.getElementById('openMobilePanel');

    if (fpvTrigger) {
      fpvTrigger.addEventListener('click', () => {
        fpvModal?.show();
      });
    }

    if (panelTrigger) {
      panelTrigger.addEventListener('click', () => {
        mobilePanel?.show();
      });
    }
  };

  const initDefaultState = () => {
    const firstCp = document.querySelector('[data-cp-id]');
    if (firstCp) {
      const cpId = firstCp.getAttribute('data-cp-id');
      if (cpId) selectConnectionPoint(cpId);
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    initOverlays();
    bindOverlayTriggers();
    bindCpButtons();
    initDefaultState();

    const saveButton = document.getElementById('saveCpBtn');
    if (saveButton) {
      saveButton.addEventListener('click', handleSaveClick);
    }
  });
})();
