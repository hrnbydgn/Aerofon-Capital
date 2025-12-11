// QGroundControl Next Gen - Advanced Drone Control System
class DroneController {
    constructor() {
        this.state = {
            connected: true,
            armed: false,
            mode: 'stabilize',
            inFlight: false
        };
        
        this.telemetry = {
            altitude: 0,
            targetAltitude: 0,
            speed: 0,
            climbRate: 0,
            heading: 0,
            roll: 0,
            pitch: 0,
            yaw: 0,
            battery: {
                voltage: 16.8,
                percent: 100,
                current: 0
            },
            gps: {
                lat: 41.0082,
                lon: 28.9784,
                satellites: 12,
                hdop: 1.2,
                fix: 3
            },
            distance: 0,
            homeDistance: 0,
            flightTime: 0
        };
        
        this.map = null;
        this.droneMarker = null;
        this.homeMarker = null;
        this.flightPath = [];
        this.pathPolyline = null;
        
        this.altitudeHistory = new Array(60).fill(0);
        this.altChart = null;
        
        this.startTime = null;
        this.lastUpdate = Date.now();
        
        this.init();
    }
    
    init() {
        console.log('🚀 Initializing QGroundControl Next Gen...');
        
        this.setupMap();
        this.setupEventListeners();
        this.setupInstruments();
        this.setupAltitudeGraph();
        this.startMainLoop();
        
        console.log('✅ System ready');
    }
    
    setupMap() {
        const mapView = document.getElementById('map-view');
        
        this.map = L.map(mapView, {
            zoomControl: true,
            attributionControl: false,
            preferCanvas: true
        }).setView([this.telemetry.gps.lat, this.telemetry.gps.lon], 17);
        
        // Satellite imagery
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 19
        }).addTo(this.map);
        
        // Drone marker
        const droneIcon = L.divIcon({
            html: `<div style="color: #ff3366; font-size: 32px; filter: drop-shadow(0 0 8px #ff3366);">
                    <i class="fas fa-location-crosshairs"></i>
                   </div>`,
            className: 'drone-icon',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        this.droneMarker = L.marker(
            [this.telemetry.gps.lat, this.telemetry.gps.lon],
            { icon: droneIcon }
        ).addTo(this.map);
        
        // Home marker
        const homeIcon = L.divIcon({
            html: `<div style="color: #00ff88; font-size: 28px; filter: drop-shadow(0 0 8px #00ff88);">
                    <i class="fas fa-house-flag"></i>
                   </div>`,
            className: 'home-icon',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
        
        this.homeMarker = L.marker(
            [this.telemetry.gps.lat, this.telemetry.gps.lon],
            { icon: homeIcon }
        ).addTo(this.map);
        
        // Flight path
        this.pathPolyline = L.polyline([], {
            color: '#00d4ff',
            weight: 3,
            opacity: 0.7
        }).addTo(this.map);
        
        // Add scale
        L.control.scale({ imperial: false }).addTo(this.map);
    }
    
    setupEventListeners() {
        // ARM button
        const armBtn = document.getElementById('armBtn');
        armBtn?.addEventListener('click', () => this.toggleArm());
        
        // Mode buttons
        document.querySelectorAll('.mode-option').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.mode-option').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                this.state.mode = btn.dataset.mode;
                console.log(`Mode changed to: ${this.state.mode.toUpperCase()}`);
            });
        });
        
        // Nav tools
        document.querySelectorAll('.nav-tool').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.nav-tool').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
        
        // Slider setup
        this.setupTakeoffSlider();
    }
    
    setupTakeoffSlider() {
        const handle = document.getElementById('sliderHandle');
        const track = handle?.parentElement;
        
        if (!handle || !track) return;
        
        let isDragging = false;
        let startX = 0;
        let currentLeft = 0;
        const maxDistance = track.offsetWidth - handle.offsetWidth - 10;
        
        const startDrag = (e) => {
            isDragging = true;
            startX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
            currentLeft = parseInt(handle.style.left) || 0;
            handle.style.transition = 'none';
        };
        
        const onDrag = (e) => {
            if (!isDragging) return;
            
            const clientX = e.type.includes('mouse') ? e.clientX : e.touches[0].clientX;
            let distance = currentLeft + (clientX - startX);
            
            distance = Math.max(0, Math.min(distance, maxDistance));
            handle.style.left = distance + 'px';
            
            // Complete action
            if (distance >= maxDistance * 0.95) {
                isDragging = false;
                this.executeTakeoff();
            }
        };
        
        const stopDrag = () => {
            if (isDragging) {
                isDragging = false;
                handle.style.transition = 'left 0.3s ease';
                handle.style.left = '0px';
            }
        };
        
        handle.addEventListener('mousedown', startDrag);
        handle.addEventListener('touchstart', startDrag);
        document.addEventListener('mousemove', onDrag);
        document.addEventListener('touchmove', onDrag);
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchend', stopDrag);
    }
    
    setupInstruments() {
        this.horizonCanvas = document.getElementById('horizonCanvas');
        this.horizonCtx = this.horizonCanvas?.getContext('2d');
        
        this.compassCanvas = document.getElementById('compassCanvas');
        this.compassCtx = this.compassCanvas?.getContext('2d');
    }
    
    setupAltitudeGraph() {
        const canvas = document.getElementById('altGraph');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        this.altChart = {
            canvas: canvas,
            ctx: ctx,
            data: new Array(60).fill(0)
        };
    }
    
    toggleArm() {
        this.state.armed = !this.state.armed;
        const armBtn = document.getElementById('armBtn');
        
        if (this.state.armed) {
            armBtn.classList.add('armed');
            armBtn.querySelector('span').textContent = 'DISARM';
            this.startTime = Date.now();
            console.log('✅ System ARMED');
        } else {
            armBtn.classList.remove('armed');
            armBtn.querySelector('span').textContent = 'ARM';
            this.state.inFlight = false;
            this.startTime = null;
            console.log('⚠️ System DISARMED');
        }
    }
    
    executeTakeoff() {
        closeTakeoffSlider();
        
        if (!this.state.armed) {
            this.toggleArm();
        }
        
        const targetAlt = parseFloat(document.getElementById('takeoffAlt')?.value) || 10;
        const climbRate = parseFloat(document.getElementById('takeoffRate')?.value) || 2.5;
        
        this.state.inFlight = true;
        this.telemetry.targetAltitude = targetAlt;
        
        console.log(`🛫 TAKEOFF initiated - Target: ${targetAlt}m, Climb rate: ${climbRate}m/s`);
        
        // Smooth climb animation
        const climbInterval = setInterval(() => {
            if (this.telemetry.altitude < this.telemetry.targetAltitude) {
                this.telemetry.altitude += climbRate * 0.1;
                this.telemetry.climbRate = climbRate;
            } else {
                this.telemetry.altitude = this.telemetry.targetAltitude;
                this.telemetry.climbRate = 0;
                clearInterval(climbInterval);
                console.log('✅ Target altitude reached');
            }
        }, 100);
    }
    
    drawHorizon() {
        if (!this.horizonCtx) return;
        
        const ctx = this.horizonCtx;
        const canvas = this.horizonCanvas;
        const cx = canvas.width / 2;
        const cy = canvas.height / 2;
        const r = 85;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(this.telemetry.roll * Math.PI / 180);
        
        // Sky
        const skyGrad = ctx.createLinearGradient(0, -r, 0, 0);
        skyGrad.addColorStop(0, '#0066ff');
        skyGrad.addColorStop(1, '#00aaff');
        ctx.fillStyle = skyGrad;
        ctx.fillRect(-r, -r - this.telemetry.pitch * 4, r * 2, r + this.telemetry.pitch * 4);
        
        // Ground
        const groundGrad = ctx.createLinearGradient(0, 0, 0, r);
        groundGrad.addColorStop(0, '#8B4513');
        groundGrad.addColorStop(1, '#654321');
        ctx.fillStyle = groundGrad;
        ctx.fillRect(-r, -this.telemetry.pitch * 4, r * 2, r * 2);
        
        // Horizon line
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(-r, -this.telemetry.pitch * 4);
        ctx.lineTo(r, -this.telemetry.pitch * 4);
        ctx.stroke();
        
        // Pitch ladder
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.lineWidth = 2;
        ctx.font = '10px monospace';
        ctx.fillStyle = '#ffffff';
        ctx.textAlign = 'center';
        
        for (let i = -30; i <= 30; i += 10) {
            if (i === 0) continue;
            const y = -this.telemetry.pitch * 4 - i * 3;
            const w = i % 20 === 0 ? 50 : 30;
            
            ctx.beginPath();
            ctx.moveTo(-w / 2, y);
            ctx.lineTo(w / 2, y);
            ctx.stroke();
            
            if (i % 20 === 0) {
                ctx.save();
                ctx.rotate(-this.telemetry.roll * Math.PI / 180);
                ctx.fillText(Math.abs(i).toString(), -w / 2 - 20, y + 4);
                ctx.fillText(Math.abs(i).toString(), w / 2 + 20, y + 4);
                ctx.restore();
            }
        }
        
        ctx.restore();
        
        // Aircraft symbol
        ctx.strokeStyle = '#ffd700';
        ctx.lineWidth = 4;
        ctx.beginPath();
        ctx.moveTo(cx - 50, cy);
        ctx.lineTo(cx - 20, cy);
        ctx.moveTo(cx + 20, cy);
        ctx.lineTo(cx + 50, cy);
        ctx.stroke();
        
        ctx.beginPath();
        ctx.arc(cx, cy, 6, 0, Math.PI * 2);
        ctx.fillStyle = '#ffd700';
        ctx.fill();
        
        // Outer ring
        ctx.strokeStyle = 'rgba(0, 212, 255, 0.3)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.stroke();
        
        // Roll indicator
        ctx.save();
        ctx.translate(cx, cy);
        
        // Roll scale
        for (let angle = -60; angle <= 60; angle += 15) {
            ctx.save();
            ctx.rotate(angle * Math.PI / 180);
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.5)';
            ctx.lineWidth = angle % 30 === 0 ? 3 : 2;
            ctx.beginPath();
            ctx.moveTo(0, -r);
            ctx.lineTo(0, -r + (angle % 30 === 0 ? 15 : 10));
            ctx.stroke();
            ctx.restore();
        }
        
        // Roll pointer
        ctx.rotate(this.telemetry.roll * Math.PI / 180);
        ctx.fillStyle = '#00d4ff';
        ctx.beginPath();
        ctx.moveTo(0, -r + 8);
        ctx.lineTo(-10, -r + 20);
        ctx.lineTo(10, -r + 20);
        ctx.closePath();
        ctx.fill();
        
        ctx.restore();
    }
    
    drawCompass() {
        if (!this.compassCtx) return;
        
        const ctx = this.compassCtx;
        const canvas = this.compassCanvas;
        const cx = canvas.width / 2;
        const cy = canvas.height / 2;
        const r = 70;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Outer ring
        ctx.strokeStyle = 'rgba(0, 212, 255, 0.3)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.stroke();
        
        ctx.strokeStyle = 'rgba(0, 212, 255, 0.2)';
        ctx.beginPath();
        ctx.arc(cx, cy, r - 15, 0, Math.PI * 2);
        ctx.stroke();
        
        // Rotate for heading
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(-this.telemetry.heading * Math.PI / 180);
        
        // Directions
        const dirs = [
            { text: 'N', angle: 0, color: '#ff3366' },
            { text: 'E', angle: 90, color: '#ffffff' },
            { text: 'S', angle: 180, color: '#ffffff' },
            { text: 'W', angle: 270, color: '#ffffff' }
        ];
        
        ctx.font = 'bold 18px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        dirs.forEach(dir => {
            ctx.save();
            ctx.rotate(dir.angle * Math.PI / 180);
            ctx.translate(0, -r + 25);
            ctx.rotate(this.telemetry.heading * Math.PI / 180);
            ctx.fillStyle = dir.color;
            ctx.fillText(dir.text, 0, 0);
            ctx.restore();
        });
        
        // Degree marks
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.4)';
        for (let i = 0; i < 360; i += 10) {
            ctx.save();
            ctx.rotate(i * Math.PI / 180);
            ctx.lineWidth = i % 30 === 0 ? 2 : 1;
            ctx.beginPath();
            ctx.moveTo(0, -r);
            ctx.lineTo(0, -r + (i % 30 === 0 ? 10 : 5));
            ctx.stroke();
            ctx.restore();
        }
        
        ctx.restore();
        
        // Heading pointer
        ctx.fillStyle = '#00d4ff';
        ctx.beginPath();
        ctx.moveTo(cx, cy - r + 8);
        ctx.lineTo(cx - 10, cy - r + 20);
        ctx.lineTo(cx + 10, cy - r + 20);
        ctx.closePath();
        ctx.fill();
        
        // Center dot
        ctx.beginPath();
        ctx.arc(cx, cy, 5, 0, Math.PI * 2);
        ctx.fillStyle = '#00d4ff';
        ctx.fill();
    }
    
    drawAltitudeGraph() {
        if (!this.altChart) return;
        
        const { ctx, canvas, data } = this.altChart;
        const w = canvas.width;
        const h = canvas.height;
        
        ctx.clearRect(0, 0, w, h);
        
        // Background grid
        ctx.strokeStyle = 'rgba(0, 212, 255, 0.1)';
        ctx.lineWidth = 1;
        for (let i = 0; i < 4; i++) {
            const y = (h / 3) * i;
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(w, y);
            ctx.stroke();
        }
        
        // Data line
        const max = Math.max(...data, 10);
        ctx.strokeStyle = '#00d4ff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        data.forEach((val, i) => {
            const x = (w / data.length) * i;
            const y = h - (val / max) * h;
            
            if (i === 0) {
                ctx.moveTo(x, y);
            } else {
                ctx.lineTo(x, y);
            }
        });
        
        ctx.stroke();
        
        // Fill area
        ctx.lineTo(w, h);
        ctx.lineTo(0, h);
        ctx.closePath();
        ctx.fillStyle = 'rgba(0, 212, 255, 0.1)';
        ctx.fill();
    }
    
    updateSimulation() {
        const dt = (Date.now() - this.lastUpdate) / 1000;
        this.lastUpdate = Date.now();
        
        if (this.state.armed && this.state.inFlight) {
            // Update heading
            this.telemetry.heading = (this.telemetry.heading + 0.3) % 360;
            
            // Update position
            const headingRad = this.telemetry.heading * Math.PI / 180;
            const speedDeg = (this.telemetry.speed / 111320) * dt;
            
            this.telemetry.gps.lat += Math.cos(headingRad) * speedDeg;
            this.telemetry.gps.lon += Math.sin(headingRad) * speedDeg;
            
            // Update marker
            this.droneMarker?.setLatLng([this.telemetry.gps.lat, this.telemetry.gps.lon]);
            
            // Update path
            this.flightPath.push([this.telemetry.gps.lat, this.telemetry.gps.lon]);
            if (this.flightPath.length > 200) this.flightPath.shift();
            this.pathPolyline?.setLatLngs(this.flightPath);
            
            // Attitude simulation
            this.telemetry.roll = Math.sin(Date.now() / 3000) * 8;
            this.telemetry.pitch = Math.sin(Date.now() / 3500) * 6;
            
            // Speed variation
            this.telemetry.speed = 5 + Math.sin(Date.now() / 5000) * 3;
            
            // Battery drain
            this.telemetry.battery.voltage -= 0.0003 * dt;
            this.telemetry.battery.percent = Math.max(0, ((this.telemetry.battery.voltage - 14.0) / 2.8) * 100);
            this.telemetry.battery.current = 18 + Math.random() * 4;
            
            // Distance calculation
            const homeLat = this.homeMarker.getLatLng().lat;
            const homeLon = this.homeMarker.getLatLng().lng;
            this.telemetry.homeDistance = this.calculateDistance(
                this.telemetry.gps.lat, this.telemetry.gps.lon,
                homeLat, homeLon
            );
            
            this.telemetry.distance += this.telemetry.speed * dt;
        }
        
        // Update altitude history
        this.altitudeHistory.shift();
        this.altitudeHistory.push(this.telemetry.altitude);
        if (this.altChart) {
            this.altChart.data = [...this.altitudeHistory];
        }
    }
    
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000; // Earth radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
    
    updateDisplay() {
        // Telemetry bar
        this.updateElement('tAlt', this.telemetry.altitude.toFixed(1) + ' m');
        this.updateElement('tSpeed', this.telemetry.speed.toFixed(1) + ' m/s');
        this.updateElement('tClimb', this.telemetry.climbRate.toFixed(1) + ' m/s');
        this.updateElement('tDist', Math.round(this.telemetry.distance) + ' m');
        this.updateElement('tHome', Math.round(this.telemetry.homeDistance) + ' m');
        this.updateElement('tGPS', this.telemetry.gps.satellites);
        this.updateElement('tHDOP', this.telemetry.gps.hdop.toFixed(1));
        
        // Right panel instruments
        this.updateElement('altMain', Math.round(this.telemetry.altitude));
        this.updateElement('speedValue', Math.round(this.telemetry.speed));
        this.updateElement('headingValue', Math.round(this.telemetry.heading) + '°');
        this.updateElement('rollVal', this.telemetry.roll.toFixed(1) + '°');
        this.updateElement('pitchVal', this.telemetry.pitch.toFixed(1) + '°');
        
        // HUD
        this.updateElement('hudLat', this.telemetry.gps.lat.toFixed(6));
        this.updateElement('hudLon', this.telemetry.gps.lon.toFixed(6));
        this.updateElement('hudAlt', Math.round(this.telemetry.altitude) + ' m');
        this.updateElement('hudSpd', this.telemetry.speed.toFixed(1) + ' m/s');
        
        // Battery
        const battPercent = Math.max(0, Math.min(100, this.telemetry.battery.percent));
        this.updateElement('battVolt', this.telemetry.battery.voltage.toFixed(1) + 'V');
        
        const battFill = document.getElementById('battFill');
        const battIcon = document.getElementById('battIcon');
        if (battFill) {
            battFill.style.width = battPercent + '%';
            battFill.classList.toggle('low', battPercent < 20);
        }
        if (battIcon) {
            if (battPercent < 20) battIcon.className = 'fas fa-battery-empty';
            else if (battPercent < 40) battIcon.className = 'fas fa-battery-quarter';
            else if (battPercent < 60) battIcon.className = 'fas fa-battery-half';
            else if (battPercent < 80) battIcon.className = 'fas fa-battery-three-quarters';
            else battIcon.className = 'fas fa-battery-full';
        }
        
        // Flight time
        if (this.startTime) {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const mins = Math.floor(elapsed / 60);
            const secs = elapsed % 60;
            this.updateElement('tTime', `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);
        }
        
        // Speed gauge (SVG arc update)
        const speedGauge = document.getElementById('speedGauge');
        if (speedGauge) {
            const maxSpeed = 20;
            const angle = (this.telemetry.speed / maxSpeed) * 180;
            const radians = (angle - 90) * Math.PI / 180;
            const x = 60 + 50 * Math.cos(radians);
            const y = 70 + 50 * Math.sin(radians);
            const largeArc = angle > 180 ? 1 : 0;
            speedGauge.setAttribute('d', `M10,70 A50,50 0 ${largeArc},1 ${x},${y}`);
        }
    }
    
    updateElement(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }
    
    startMainLoop() {
        const loop = () => {
            this.updateSimulation();
            this.updateDisplay();
            this.drawHorizon();
            this.drawCompass();
            this.drawAltitudeGraph();
            requestAnimationFrame(loop);
        };
        loop();
    }
}

// Global functions for UI interactions
function toggleView() {
    const mapView = document.getElementById('map-view');
    const cameraView = document.getElementById('camera-view');
    
    if (!mapView || !cameraView) return;
    
    mapView.classList.toggle('pip-mode');
    cameraView.classList.toggle('pip-mode');
    
    // Refresh map after transition
    setTimeout(() => {
        if (window.droneController?.map) {
            window.droneController.map.invalidateSize();
        }
    }, 600);
}

function showTakeoffSlider() {
    const modal = document.getElementById('takeoffModal');
    if (modal) {
        modal.classList.add('show');
        
        // Reset slider
        const handle = document.getElementById('sliderHandle');
        if (handle) {
            handle.style.left = '0px';
        }
    }
}

function closeTakeoffSlider() {
    const modal = document.getElementById('takeoffModal');
    if (modal) {
        modal.classList.remove('show');
    }
}

// Click outside to close modal
document.addEventListener('click', (e) => {
    const modal = document.getElementById('takeoffModal');
    if (modal && e.target === modal) {
        closeTakeoffSlider();
    }
});

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎯 QGroundControl Next Gen - Initializing...');
    window.droneController = new DroneController();
});

// Add PIP click handler
document.addEventListener('DOMContentLoaded', () => {
    const mapView = document.getElementById('map-view');
    const cameraView = document.getElementById('camera-view');
    
    if (mapView && cameraView) {
        mapView.addEventListener('click', (e) => {
            if (mapView.classList.contains('pip-mode')) {
                toggleView();
            }
        });
        
        cameraView.addEventListener('click', (e) => {
            if (cameraView.classList.contains('pip-mode')) {
                toggleView();
            }
        });
    }
});
