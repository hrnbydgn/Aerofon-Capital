// Drone Ground Control Application
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
            climbrate: 0,
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
        
        this.startTime = null;
        this.lastUpdate = Date.now();
        
        this.init();
    }
    
    init() {
        console.log('🚁 Drone Ground Control - Initializing...');
        
        this.setupMap();
        this.setupEventListeners();
        this.setupInstruments();
        this.startSimulation();
        this.startMainLoop();
        
        console.log('✅ System ready');
    }
    
    setupMap() {
        const mapContainer = document.getElementById('map-container');
        
        this.map = L.map(mapContainer, {
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
            html: `<div style="color: #ff3b30; font-size: 28px; filter: drop-shadow(0 0 6px #ff3b30);">
                    <i class="fas fa-location-crosshairs"></i>
                   </div>`,
            className: 'drone-icon',
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });
        
        this.droneMarker = L.marker(
            [this.telemetry.gps.lat, this.telemetry.gps.lon],
            { icon: droneIcon }
        ).addTo(this.map);
        
        // Home marker
        const homeIcon = L.divIcon({
            html: `<div style="color: #00ff88; font-size: 24px; filter: drop-shadow(0 0 6px #00ff88);">
                    <i class="fas fa-house-flag"></i>
                   </div>`,
            className: 'home-icon',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        this.homeMarker = L.marker(
            [this.telemetry.gps.lat, this.telemetry.gps.lon],
            { icon: homeIcon }
        ).addTo(this.map);
        
        // Flight path
        this.pathPolyline = L.polyline([], {
            color: '#0084ff',
            weight: 3,
            opacity: 0.7
        }).addTo(this.map);
    }
    
    setupEventListeners() {
        // ARM button
        const armBtn = document.getElementById('armBtn');
        armBtn?.addEventListener('click', () => this.toggleArm());
        
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
    
    toggleArm() {
        this.state.armed = !this.state.armed;
        const armBtn = document.getElementById('armBtn');
        
        if (this.state.armed) {
            armBtn.classList.add('armed');
            armBtn.innerHTML = '<i class="fas fa-power-off"></i> DISARM';
            this.startTime = Date.now();
            console.log('✅ System ARMED');
        } else {
            armBtn.classList.remove('armed');
            armBtn.innerHTML = '<i class="fas fa-power-off"></i> ARM';
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
        
        console.log(`🛫 TAKEOFF - Target: ${targetAlt}m, Rate: ${climbRate}m/s`);
        
        const climbInterval = setInterval(() => {
            if (this.telemetry.altitude < this.telemetry.targetAltitude) {
                this.telemetry.altitude += climbRate * 0.1;
                this.telemetry.climbrate = climbRate;
            } else {
                this.telemetry.altitude = this.telemetry.targetAltitude;
                this.telemetry.climbrate = 0;
                clearInterval(climbInterval);
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
        
        ctx.restore();
        
        // Aircraft symbol
        ctx.strokeStyle = '#0084ff';
        ctx.lineWidth = 4;
        ctx.beginPath();
        ctx.moveTo(cx - 40, cy);
        ctx.lineTo(cx - 15, cy);
        ctx.moveTo(cx + 15, cy);
        ctx.lineTo(cx + 40, cy);
        ctx.stroke();
        
        ctx.beginPath();
        ctx.arc(cx, cy, 5, 0, Math.PI * 2);
        ctx.fillStyle = '#0084ff';
        ctx.fill();
    }
    
    drawCompass() {
        if (!this.compassCtx) return;
        
        const ctx = this.compassCtx;
        const canvas = this.compassCanvas;
        const cx = canvas.width / 2;
        const cy = canvas.height / 2;
        const r = 65;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Outer circle
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(cx, cy, r, 0, Math.PI * 2);
        ctx.stroke();
        
        ctx.save();
        ctx.translate(cx, cy);
        ctx.rotate(-this.telemetry.heading * Math.PI / 180);
        
        // Directions
        const dirs = [
            { text: 'N', angle: 0, color: '#ff3b30' },
            { text: 'E', angle: 90, color: '#fff' },
            { text: 'S', angle: 180, color: '#fff' },
            { text: 'W', angle: 270, color: '#fff' }
        ];
        
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        dirs.forEach(dir => {
            ctx.save();
            ctx.rotate(dir.angle * Math.PI / 180);
            ctx.translate(0, -r + 20);
            ctx.rotate(this.telemetry.heading * Math.PI / 180);
            ctx.fillStyle = dir.color;
            ctx.fillText(dir.text, 0, 0);
            ctx.restore();
        });
        
        ctx.restore();
        
        // Heading pointer
        ctx.fillStyle = '#0084ff';
        ctx.beginPath();
        ctx.moveTo(cx, cy - r + 8);
        ctx.lineTo(cx - 8, cy - r + 18);
        ctx.lineTo(cx + 8, cy - r + 18);
        ctx.closePath();
        ctx.fill();
    }
    
    startSimulation() {
        setInterval(() => {
            if (this.state.armed && this.state.inFlight) {
                this.telemetry.heading = (this.telemetry.heading + 0.3) % 360;
                
                const headingRad = this.telemetry.heading * Math.PI / 180;
                const speedDeg = (this.telemetry.speed / 111320) * 0.1;
                
                this.telemetry.gps.lat += Math.cos(headingRad) * speedDeg;
                this.telemetry.gps.lon += Math.sin(headingRad) * speedDeg;
                
                this.droneMarker?.setLatLng([this.telemetry.gps.lat, this.telemetry.gps.lon]);
                
                this.flightPath.push([this.telemetry.gps.lat, this.telemetry.gps.lon]);
                if (this.flightPath.length > 200) this.flightPath.shift();
                this.pathPolyline?.setLatLngs(this.flightPath);
                
                this.telemetry.roll = Math.sin(Date.now() / 3000) * 8;
                this.telemetry.pitch = Math.sin(Date.now() / 3500) * 6;
                
                this.telemetry.speed = 5 + Math.sin(Date.now() / 5000) * 3;
                
                this.telemetry.battery.voltage -= 0.0003;
                this.telemetry.battery.percent = Math.max(0, ((this.telemetry.battery.voltage - 14.0) / 2.8) * 100);
                
                const homeLat = this.homeMarker.getLatLng().lat;
                const homeLon = this.homeMarker.getLatLng().lng;
                this.telemetry.homeDistance = this.calculateDistance(
                    this.telemetry.gps.lat, this.telemetry.gps.lon,
                    homeLat, homeLon
                );
                
                this.telemetry.distance += this.telemetry.speed * 0.1;
            }
        }, 100);
    }
    
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }
    
    updateDisplay() {
        // Left panel cards
        this.updateElement('cardAlt', Math.round(this.telemetry.altitude));
        this.updateElement('cardSpeed', this.telemetry.speed.toFixed(1));
        this.updateElement('cardClimb', this.telemetry.climbrate.toFixed(1));
        this.updateElement('cardDist', Math.round(this.telemetry.distance));
        this.updateElement('cardGPS', this.telemetry.gps.satellites);
        
        // Battery card
        const battPercent = Math.max(0, Math.min(100, this.telemetry.battery.percent));
        this.updateElement('cardBattPercent', Math.round(battPercent));
        
        const battCard = document.getElementById('batteryCard');
        const battFill = document.getElementById('cardBattFill');
        const battIcon = document.getElementById('cardBattIcon');
        
        if (battFill) {
            battFill.style.width = battPercent + '%';
            battFill.classList.remove('warning', 'critical');
            
            if (battPercent < 20) {
                battFill.classList.add('critical');
                battCard?.classList.add('critical');
                battCard?.classList.remove('warning');
            } else if (battPercent < 40) {
                battFill.classList.add('warning');
                battCard?.classList.add('warning');
                battCard?.classList.remove('critical');
            } else {
                battCard?.classList.remove('warning', 'critical');
            }
        }
        
        if (battIcon) {
            if (battPercent < 20) battIcon.className = 'fas fa-battery-empty';
            else if (battPercent < 40) battIcon.className = 'fas fa-battery-quarter';
            else if (battPercent < 60) battIcon.className = 'fas fa-battery-half';
            else if (battPercent < 80) battIcon.className = 'fas fa-battery-three-quarters';
            else battIcon.className = 'fas fa-battery-full';
        }
        
        // Attitude values
        this.updateElement('rollValue', this.telemetry.roll.toFixed(1) + '°');
        this.updateElement('pitchValue', this.telemetry.pitch.toFixed(1) + '°');
        
        // Compass heading
        this.updateElement('compassHeading', Math.round(this.telemetry.heading) + '°');
        
        // GPS details
        this.updateElement('gpsLat', this.telemetry.gps.lat.toFixed(6));
        this.updateElement('gpsLon', this.telemetry.gps.lon.toFixed(6));
        this.updateElement('gpsHDOP', this.telemetry.gps.hdop.toFixed(1));
        this.updateElement('gpsHome', Math.round(this.telemetry.homeDistance) + ' m');
        
        // HUD
        this.updateElement('hudLat', this.telemetry.gps.lat.toFixed(6));
        this.updateElement('hudLon', this.telemetry.gps.lon.toFixed(6));
        this.updateElement('hudAlt', Math.round(this.telemetry.altitude) + ' m');
        
        // Top bar flight time
        if (this.startTime) {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const mins = Math.floor(elapsed / 60);
            const secs = elapsed % 60;
            this.updateElement('topFlightTime', `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);
        }
    }
    
    updateElement(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }
    
    startMainLoop() {
        const loop = () => {
            this.updateDisplay();
            this.drawHorizon();
            this.drawCompass();
            requestAnimationFrame(loop);
        };
        loop();
    }
}

// Camera fullscreen toggle
let cameraFullscreen = false;

function toggleCameraFullscreen() {
    cameraFullscreen = !cameraFullscreen;
    const cameraView = document.getElementById('camera-view');
    const mapContainer = document.getElementById('map-container');
    
    if (cameraFullscreen) {
        cameraView.style.cssText = `
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 0;
            z-index: 100;
        `;
        mapContainer.style.display = 'none';
    } else {
        cameraView.style.cssText = '';
        mapContainer.style.display = 'block';
        
        setTimeout(() => {
            if (window.droneController?.map) {
                window.droneController.map.invalidateSize();
            }
        }, 100);
    }
}

function showTakeoffSlider() {
    const modal = document.getElementById('takeoffModal');
    if (modal) {
        modal.classList.add('show');
        const handle = document.getElementById('sliderHandle');
        if (handle) handle.style.left = '0px';
    }
}

function closeTakeoffSlider() {
    const modal = document.getElementById('takeoffModal');
    if (modal) modal.classList.remove('show');
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
    console.log('🚁 Drone Ground Control - Initializing...');
    window.droneController = new DroneController();
});
