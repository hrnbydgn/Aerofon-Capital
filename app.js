// QGroundControl Web Application
class QGroundControl {
    constructor() {
        this.connected = true;
        this.armed = false;
        this.flightMode = 'STABILIZE';
        this.map = null;
        this.droneMarker = null;
        
        this.telemetry = {
            altitude: 0,
            groundspeed: 0,
            airspeed: 0,
            climbrate: 0,
            heading: 0,
            roll: 0,
            pitch: 0,
            yaw: 0,
            battery: {
                voltage: 16.8,
                percent: 100,
                current: 0.0
            },
            gps: {
                fix: 3,
                satellites: 12,
                hdop: 1.2,
                lat: 41.0082,
                lon: 28.9784
            },
            signals: {
                rc: 100,
                telemetry: 100
            },
            distance: 0,
            homeDistance: 0
        };
        
        this.flightTime = 0;
        this.flightStartTime = null;
        this.animationFrame = null;
        
        this.init();
    }
    
    init() {
        console.log('QGroundControl initializing...');
        this.setupMap();
        this.setupEventListeners();
        this.setupInstruments();
        this.startSimulation();
        this.startAnimationLoop();
    }
    
    setupMap() {
        // Initialize map
        this.map = L.map('map', {
            zoomControl: true,
            attributionControl: false
        }).setView([this.telemetry.gps.lat, this.telemetry.gps.lon], 18);
        
        // Satellite imagery tile layer
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19
        }).addTo(this.map);
        
        // Drone marker with icon
        const droneIcon = L.divIcon({
            html: '<div class="drone-marker"><i class="fas fa-plane"></i></div>',
            className: 'custom-drone-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        this.droneMarker = L.marker(
            [this.telemetry.gps.lat, this.telemetry.gps.lon],
            { icon: droneIcon }
        ).addTo(this.map);
        
        // Home marker
        const homeIcon = L.divIcon({
            html: '<div style="color: #28a745; font-size: 24px;"><i class="fas fa-home"></i></div>',
            className: 'home-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        L.marker([this.telemetry.gps.lat, this.telemetry.gps.lon], { icon: homeIcon }).addTo(this.map);
    }
    
    setupEventListeners() {
        // ARM button
        const armBtn = document.getElementById('armBtn');
        if (armBtn) {
            armBtn.addEventListener('click', () => this.toggleArm());
        }
        
        // Tool buttons
        document.querySelectorAll('.tool-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (btn.dataset.tool) {
                    document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                }
            });
        });
        
        // Slider
        this.setupSlider();
    }
    
    setupSlider() {
        const sliderThumb = document.getElementById('sliderThumb');
        const sliderContainer = document.getElementById('takeoffSlider');
        
        if (!sliderThumb || !sliderContainer) return;
        
        let isDragging = false;
        let startX = 0;
        let currentX = 0;
        const maxDistance = 250; // 320 - 70 (track width - thumb width)
        
        const startDrag = (e) => {
            isDragging = true;
            startX = e.type === 'mousedown' ? e.clientX : e.touches[0].clientX;
            currentX = parseInt(sliderThumb.style.left) || 0;
        };
        
        const onDrag = (e) => {
            if (!isDragging) return;
            
            const clientX = e.type === 'mousemove' ? e.clientX : e.touches[0].clientX;
            let distance = currentX + (clientX - startX);
            
            if (distance < 0) distance = 0;
            if (distance > maxDistance) distance = maxDistance;
            
            sliderThumb.style.left = distance + 'px';
            
            // Complete takeoff
            if (distance >= maxDistance) {
                isDragging = false;
                this.executeTakeoff();
            }
        };
        
        const stopDrag = () => {
            if (isDragging) {
                isDragging = false;
                sliderThumb.style.left = '0px';
            }
        };
        
        sliderThumb.addEventListener('mousedown', startDrag);
        sliderThumb.addEventListener('touchstart', startDrag);
        
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('touchmove', onDrag);
        
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchend', stopDrag);
    }
    
    setupInstruments() {
        this.attitudeCanvas = document.getElementById('attitudeCanvas');
        this.attitudeCtx = this.attitudeCanvas ? this.attitudeCanvas.getContext('2d') : null;
        
        this.compassCanvas = document.getElementById('compassCanvas');
        this.compassCtx = this.compassCanvas ? this.compassCanvas.getContext('2d') : null;
    }
    
    toggleArm() {
        this.armed = !this.armed;
        const armBtn = document.getElementById('armBtn');
        
        if (this.armed) {
            armBtn.classList.add('armed');
            armBtn.querySelector('span').textContent = 'DISARM';
            this.flightStartTime = Date.now();
        } else {
            armBtn.classList.remove('armed');
            armBtn.querySelector('span').textContent = 'ARM';
            this.flightStartTime = null;
            this.flightTime = 0;
        }
    }
    
    executeTakeoff() {
        const slider = document.getElementById('takeoffSlider');
        if (slider) {
            slider.classList.remove('show');
        }
        
        if (!this.armed) {
            this.toggleArm();
        }
        
        // Simulate takeoff
        let targetAltitude = 10;
        let takeoffInterval = setInterval(() => {
            if (this.telemetry.altitude < targetAltitude) {
                this.telemetry.altitude += 0.5;
                this.telemetry.climbrate = 2.0;
            } else {
                this.telemetry.climbrate = 0;
                clearInterval(takeoffInterval);
            }
        }, 100);
    }
    
    drawAttitudeIndicator() {
        if (!this.attitudeCtx) return;
        
        const ctx = this.attitudeCtx;
        const canvas = this.attitudeCanvas;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 75;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Save context
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(this.telemetry.roll * Math.PI / 180);
        
        // Sky (top half)
        const gradient1 = ctx.createLinearGradient(0, -radius, 0, 0);
        gradient1.addColorStop(0, '#1e88e5');
        gradient1.addColorStop(1, '#42a5f5');
        ctx.fillStyle = gradient1;
        ctx.fillRect(-radius, -radius - this.telemetry.pitch * 3, radius * 2, radius + this.telemetry.pitch * 3);
        
        // Ground (bottom half)
        const gradient2 = ctx.createLinearGradient(0, 0, 0, radius);
        gradient2.addColorStop(0, '#6d4c41');
        gradient2.addColorStop(1, '#5d4037');
        ctx.fillStyle = gradient2;
        ctx.fillRect(-radius, -this.telemetry.pitch * 3, radius * 2, radius * 2);
        
        // Horizon line
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(-radius, -this.telemetry.pitch * 3);
        ctx.lineTo(radius, -this.telemetry.pitch * 3);
        ctx.stroke();
        
        // Pitch ladder
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.lineWidth = 2;
        ctx.font = '10px monospace';
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        
        for (let i = -30; i <= 30; i += 10) {
            if (i === 0) continue;
            const y = -this.telemetry.pitch * 3 - i * 2;
            const lineWidth = i % 20 === 0 ? 40 : 25;
            
            ctx.beginPath();
            ctx.moveTo(-lineWidth / 2, y);
            ctx.lineTo(lineWidth / 2, y);
            ctx.stroke();
            
            if (i % 20 === 0) {
                ctx.fillText(i.toString(), -lineWidth / 2 - 15, y + 4);
                ctx.fillText(i.toString(), lineWidth / 2 + 15, y + 4);
            }
        }
        
        ctx.restore();
        
        // Center reference (aircraft symbol)
        ctx.strokeStyle = '#ffc107';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(centerX - 40, centerY);
        ctx.lineTo(centerX - 15, centerY);
        ctx.moveTo(centerX + 15, centerY);
        ctx.lineTo(centerX + 40, centerY);
        ctx.stroke();
        
        ctx.beginPath();
        ctx.arc(centerX, centerY, 5, 0, 2 * Math.PI);
        ctx.fillStyle = '#ffc107';
        ctx.fill();
        
        // Outer circle
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.stroke();
        
        // Roll indicator
        ctx.save();
        ctx.translate(centerX, centerY);
        
        // Roll scale
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.lineWidth = 2;
        for (let angle = -60; angle <= 60; angle += 15) {
            ctx.save();
            ctx.rotate(angle * Math.PI / 180);
            ctx.beginPath();
            const tickLength = angle % 30 === 0 ? 12 : 8;
            ctx.moveTo(0, -radius);
            ctx.lineTo(0, -radius + tickLength);
            ctx.stroke();
            ctx.restore();
        }
        
        // Roll pointer
        ctx.rotate(this.telemetry.roll * Math.PI / 180);
        ctx.fillStyle = '#ffc107';
        ctx.beginPath();
        ctx.moveTo(0, -radius + 5);
        ctx.lineTo(-8, -radius + 15);
        ctx.lineTo(8, -radius + 15);
        ctx.closePath();
        ctx.fill();
        
        ctx.restore();
    }
    
    drawCompass() {
        if (!this.compassCtx) return;
        
        const ctx = this.compassCtx;
        const canvas = this.compassCanvas;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 60;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Outer circle
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.3)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.stroke();
        
        // Inner circle
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius - 10, 0, 2 * Math.PI);
        ctx.stroke();
        
        // Rotate context for heading
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(-this.telemetry.heading * Math.PI / 180);
        
        // Draw cardinal directions
        const directions = [
            { text: 'N', angle: 0, color: '#dc3545' },
            { text: 'E', angle: 90, color: '#ffffff' },
            { text: 'S', angle: 180, color: '#ffffff' },
            { text: 'W', angle: 270, color: '#ffffff' }
        ];
        
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        directions.forEach(dir => {
            ctx.save();
            ctx.rotate(dir.angle * Math.PI / 180);
            ctx.translate(0, -radius + 20);
            ctx.rotate(this.telemetry.heading * Math.PI / 180);
            ctx.fillStyle = dir.color;
            ctx.fillText(dir.text, 0, 0);
            ctx.restore();
        });
        
        // Draw degree ticks
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
        ctx.lineWidth = 1;
        for (let i = 0; i < 360; i += 10) {
            ctx.save();
            ctx.rotate(i * Math.PI / 180);
            ctx.beginPath();
            const tickLength = i % 30 === 0 ? 8 : 4;
            ctx.moveTo(0, -radius);
            ctx.lineTo(0, -radius + tickLength);
            ctx.stroke();
            ctx.restore();
        }
        
        ctx.restore();
        
        // Draw heading pointer (fixed at top)
        ctx.fillStyle = '#ffc107';
        ctx.beginPath();
        ctx.moveTo(centerX, centerY - radius + 5);
        ctx.lineTo(centerX - 8, centerY - radius + 15);
        ctx.lineTo(centerX + 8, centerY - radius + 15);
        ctx.closePath();
        ctx.fill();
        
        // Draw center dot
        ctx.beginPath();
        ctx.arc(centerX, centerY, 4, 0, 2 * Math.PI);
        ctx.fillStyle = '#007bff';
        ctx.fill();
    }
    
    startSimulation() {
        // Simulate telemetry updates
        setInterval(() => {
            if (this.armed) {
                // Update heading (rotate)
                this.telemetry.heading = (this.telemetry.heading + 0.5) % 360;
                
                // Simulate movement
                const headingRad = this.telemetry.heading * Math.PI / 180;
                const speed = this.telemetry.groundspeed / 111320; // m/s to degrees
                this.telemetry.gps.lat += Math.cos(headingRad) * speed * 0.1;
                this.telemetry.gps.lon += Math.sin(headingRad) * speed * 0.1;
                
                // Update drone position on map
                if (this.droneMarker) {
                    this.droneMarker.setLatLng([this.telemetry.gps.lat, this.telemetry.gps.lon]);
                    
                    // Rotate drone icon
                    const droneElement = this.droneMarker.getElement();
                    if (droneElement) {
                        const icon = droneElement.querySelector('.drone-marker i');
                        if (icon) {
                            icon.style.transform = `rotate(${this.telemetry.heading - 45}deg)`;
                        }
                    }
                }
                
                // Random variations
                this.telemetry.roll = Math.sin(Date.now() / 2000) * 10;
                this.telemetry.pitch = Math.sin(Date.now() / 2500) * 8;
                this.telemetry.groundspeed = 5 + Math.random() * 3;
                
                if (Math.random() < 0.3) {
                    this.telemetry.climbrate = (Math.random() - 0.5) * 2;
                }
                
                // Battery drain
                this.telemetry.battery.voltage -= 0.0005;
                this.telemetry.battery.percent = Math.max(0, ((this.telemetry.battery.voltage - 14.0) / (16.8 - 14.0)) * 100);
                this.telemetry.battery.current = 15 + Math.random() * 5;
                
                // Distance calculation
                this.telemetry.distance += this.telemetry.groundspeed * 0.1;
            }
        }, 100);
    }
    
    startAnimationLoop() {
        const animate = () => {
            this.updateDisplay();
            this.drawAttitudeIndicator();
            this.drawCompass();
            this.animationFrame = requestAnimationFrame(animate);
        };
        animate();
    }
    
    updateDisplay() {
        // Telemetry values
        this.updateElement('altitude', this.telemetry.altitude.toFixed(1) + ' m');
        this.updateElement('groundspeed', this.telemetry.groundspeed.toFixed(1) + ' m/s');
        this.updateElement('climbrate', this.telemetry.climbrate.toFixed(1) + ' m/s');
        this.updateElement('homeDistance', Math.round(this.telemetry.distance) + ' m');
        this.updateElement('gpsSats', this.telemetry.gps.satellites);
        
        // Heading display
        this.updateElement('headingDisplay', Math.round(this.telemetry.heading) + '°');
        
        // Battery
        const batteryIcon = document.getElementById('batteryIcon');
        const batteryVoltage = document.getElementById('batteryVoltage');
        if (batteryIcon && batteryVoltage) {
            batteryVoltage.textContent = this.telemetry.battery.voltage.toFixed(1) + 'V';
            
            if (this.telemetry.battery.percent < 20) {
                batteryIcon.className = 'fas fa-battery-empty';
                batteryIcon.style.color = '#dc3545';
            } else if (this.telemetry.battery.percent < 50) {
                batteryIcon.className = 'fas fa-battery-half';
                batteryIcon.style.color = '#ffc107';
            } else {
                batteryIcon.className = 'fas fa-battery-full';
                batteryIcon.style.color = '#28a745';
            }
        }
        
        // Flight time
        if (this.flightStartTime) {
            const elapsed = Math.floor((Date.now() - this.flightStartTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            this.updateElement('flightTime', 
                `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`);
        }
    }
    
    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }
}

// Swap Views Function (Global)
function swapViews() {
    const mapContainer = document.getElementById('map-container');
    const cameraContainer = document.getElementById('camera-container');
    
    if (!mapContainer || !cameraContainer) return;
    
    const isMapFullscreen = mapContainer.classList.contains('mode-fullscreen');
    
    if (isMapFullscreen) {
        // Swap: Map to PIP, Camera to Fullscreen
        mapContainer.classList.remove('mode-fullscreen');
        mapContainer.classList.add('mode-pip');
        
        cameraContainer.classList.remove('mode-pip');
        cameraContainer.classList.add('mode-fullscreen');
        
        // Update click handlers
        cameraContainer.removeAttribute('onclick');
        mapContainer.setAttribute('onclick', 'swapViews()');
    } else {
        // Swap: Camera to PIP, Map to Fullscreen
        mapContainer.classList.remove('mode-pip');
        mapContainer.classList.add('mode-fullscreen');
        
        cameraContainer.classList.remove('mode-fullscreen');
        cameraContainer.classList.add('mode-pip');
        
        // Update click handlers
        mapContainer.removeAttribute('onclick');
        cameraContainer.setAttribute('onclick', 'swapViews()');
    }
    
    // Invalidate map size after animation
    setTimeout(() => {
        if (window.qgcApp && window.qgcApp.map) {
            window.qgcApp.map.invalidateSize();
        }
    }, 500);
}

// Toggle Takeoff Slider (Global)
function toggleTakeoffSlider() {
    const slider = document.getElementById('takeoffSlider');
    if (slider) {
        slider.classList.toggle('show');
        
        // Reset thumb position
        const thumb = document.getElementById('sliderThumb');
        if (thumb) {
            thumb.style.left = '0px';
        }
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('Initializing QGroundControl...');
    window.qgcApp = new QGroundControl();
});
