// Drone GCS Application
class DroneGCS {
    constructor() {
        this.connected = false;
        this.armed = false;
        this.flightMode = 'STABILIZE';
        this.telemetry = {
            altitude: 0,
            groundspeed: 0,
            airspeed: 0,
            climbrate: 0,
            heading: 0,
            roll: 0,
            pitch: 0,
            battery: {
                voltage: 16.8,
                percent: 100,
                current: 0
            },
            gps: {
                fix: 0,
                satellites: 0,
                hdop: 99.9,
                lat: 39.9334,
                lon: 32.8597
            },
            rcSignal: 0,
            telemetrySignal: 0
        };
        this.flightTime = 0;
        this.map = null;
        this.planMap = null;
        this.droneMarker = null;
        
        this.init();
    }
    
    init() {
        this.setupMaps();
        this.setupEventListeners();
        this.setupInstruments();
        this.startSimulation();
        this.startUpdateLoop();
    }
    
    setupMaps() {
        // Main map
        this.map = L.map('map').setView([39.9334, 32.8597], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.map);
        
        // Drone marker
        const droneIcon = L.divIcon({
            className: 'drone-marker',
            html: `<div style="color: #4CAF50; font-size: 24px;">▲</div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });
        
        this.droneMarker = L.marker([39.9334, 32.8597], {icon: droneIcon}).addTo(this.map);
        
        // Plan map
        this.planMap = L.map('planMap').setView([39.9334, 32.8597], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(this.planMap);
    }
    
    setupEventListeners() {
        // View tabs
        document.querySelectorAll('.view-tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                document.querySelectorAll('.view-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.view-content').forEach(v => v.classList.remove('active'));
                
                e.target.classList.add('active');
                const view = e.target.dataset.view;
                document.getElementById(`${view}View`).classList.add('active');
                
                // Invalidate map size when switching to map views
                if (view === 'map') {
                    setTimeout(() => this.map.invalidateSize(), 100);
                } else if (view === 'plan') {
                    setTimeout(() => this.planMap.invalidateSize(), 100);
                }
            });
        });
        
        // Flight mode buttons
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                this.flightMode = e.target.textContent;
                this.addMessage(`Uçuş modu: ${this.flightMode}`, 'info');
            });
        });
        
        // ARM button
        document.getElementById('armBtn').addEventListener('click', (e) => {
            this.armed = !this.armed;
            e.target.classList.toggle('armed');
            e.target.textContent = this.armed ? 'DISARM' : 'ARM';
            this.addMessage(this.armed ? 'Sistem arm edildi' : 'Sistem disarm edildi', 
                           this.armed ? 'warning' : 'info');
        });
        
        // Action buttons
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const action = e.target.textContent;
                this.addMessage(`Komut gönderildi: ${action}`, 'info');
            });
        });
    }
    
    setupInstruments() {
        this.attitudeCanvas = document.getElementById('attitudeCanvas');
        this.attitudeCtx = this.attitudeCanvas.getContext('2d');
        
        this.compassCanvas = document.getElementById('compassCanvas');
        this.compassCtx = this.compassCanvas.getContext('2d');
    }
    
    drawAttitudeIndicator() {
        const ctx = this.attitudeCtx;
        const canvas = this.attitudeCanvas;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 80;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Save context
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(this.telemetry.roll * Math.PI / 180);
        
        // Sky
        ctx.fillStyle = '#4A90E2';
        ctx.fillRect(-radius, -radius - this.telemetry.pitch * 2, radius * 2, radius + this.telemetry.pitch * 2);
        
        // Ground
        ctx.fillStyle = '#8B4513';
        ctx.fillRect(-radius, -this.telemetry.pitch * 2, radius * 2, radius * 2);
        
        // Horizon line
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(-radius, -this.telemetry.pitch * 2);
        ctx.lineTo(radius, -this.telemetry.pitch * 2);
        ctx.stroke();
        
        ctx.restore();
        
        // Center reference
        ctx.strokeStyle = '#FFC107';
        ctx.lineWidth = 3;
        ctx.beginPath();
        ctx.moveTo(centerX - 30, centerY);
        ctx.lineTo(centerX - 10, centerY);
        ctx.moveTo(centerX + 10, centerY);
        ctx.lineTo(centerX + 30, centerY);
        ctx.stroke();
        
        ctx.beginPath();
        ctx.arc(centerX, centerY, 5, 0, 2 * Math.PI);
        ctx.stroke();
        
        // Outer circle
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.stroke();
    }
    
    drawCompass() {
        const ctx = this.compassCtx;
        const canvas = this.compassCanvas;
        const centerX = canvas.width / 2;
        const centerY = canvas.height / 2;
        const radius = 60;
        
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Outer circle
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.stroke();
        
        // Rotate for heading
        ctx.save();
        ctx.translate(centerX, centerY);
        ctx.rotate(-this.telemetry.heading * Math.PI / 180);
        
        // Cardinal directions
        const directions = ['N', 'E', 'S', 'W'];
        const angles = [0, 90, 180, 270];
        
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 16px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        directions.forEach((dir, i) => {
            const angle = angles[i] * Math.PI / 180;
            const x = Math.sin(angle) * (radius - 20);
            const y = -Math.cos(angle) * (radius - 20);
            
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(this.telemetry.heading * Math.PI / 180);
            
            if (dir === 'N') {
                ctx.fillStyle = '#f44336';
            } else {
                ctx.fillStyle = '#ffffff';
            }
            
            ctx.fillText(dir, 0, 0);
            ctx.restore();
        });
        
        ctx.restore();
        
        // Heading pointer
        ctx.fillStyle = '#4CAF50';
        ctx.beginPath();
        ctx.moveTo(centerX, centerY - radius + 10);
        ctx.lineTo(centerX - 8, centerY - radius + 25);
        ctx.lineTo(centerX + 8, centerY - radius + 25);
        ctx.closePath();
        ctx.fill();
    }
    
    startSimulation() {
        // Simulate connection after 2 seconds
        setTimeout(() => {
            this.connected = true;
            document.querySelector('.status-indicator').classList.remove('disconnected');
            document.querySelector('.status-indicator').classList.add('connected');
            document.querySelector('.status-text').textContent = 'Bağlı';
            this.addMessage('Drone bağlantısı kuruldu', 'info');
            
            // Simulate GPS fix
            setTimeout(() => {
                this.telemetry.gps.fix = 3;
                this.telemetry.gps.satellites = 12;
                this.telemetry.gps.hdop = 1.2;
                this.addMessage('GPS fix alındı', 'info');
            }, 1000);
        }, 2000);
        
        // Simulate telemetry updates
        setInterval(() => {
            if (this.connected && this.armed) {
                // Simulate flight
                this.telemetry.altitude += (Math.random() - 0.5) * 2;
                this.telemetry.altitude = Math.max(0, this.telemetry.altitude);
                
                this.telemetry.groundspeed = 5 + Math.random() * 3;
                this.telemetry.airspeed = this.telemetry.groundspeed + Math.random() * 2;
                this.telemetry.climbrate = (Math.random() - 0.5) * 2;
                
                this.telemetry.heading = (this.telemetry.heading + 1) % 360;
                this.telemetry.roll = Math.sin(Date.now() / 1000) * 15;
                this.telemetry.pitch = Math.sin(Date.now() / 1500) * 10;
                
                this.telemetry.battery.voltage -= 0.001;
                this.telemetry.battery.percent = ((this.telemetry.battery.voltage - 14.8) / (16.8 - 14.8)) * 100;
                this.telemetry.battery.current = 15 + Math.random() * 5;
                
                this.telemetry.rcSignal = 95 + Math.random() * 5;
                this.telemetry.telemetrySignal = 90 + Math.random() * 10;
                
                // Update drone position on map
                const newLat = this.telemetry.gps.lat + (Math.random() - 0.5) * 0.001;
                const newLon = this.telemetry.gps.lon + (Math.random() - 0.5) * 0.001;
                this.telemetry.gps.lat = newLat;
                this.telemetry.gps.lon = newLon;
                this.droneMarker.setLatLng([newLat, newLon]);
                
                this.flightTime++;
            }
        }, 100);
    }
    
    startUpdateLoop() {
        setInterval(() => {
            this.updateDisplay();
            this.drawAttitudeIndicator();
            this.drawCompass();
        }, 50);
    }
    
    updateDisplay() {
        // Telemetry
        document.getElementById('altitude').textContent = this.telemetry.altitude.toFixed(1) + ' m';
        document.getElementById('groundspeed').textContent = this.telemetry.groundspeed.toFixed(1) + ' m/s';
        document.getElementById('airspeed').textContent = this.telemetry.airspeed.toFixed(1) + ' m/s';
        document.getElementById('climbrate').textContent = this.telemetry.climbrate.toFixed(1) + ' m/s';
        document.getElementById('heading').textContent = Math.round(this.telemetry.heading) + '°';
        
        // Battery
        const batteryPercent = Math.max(0, Math.min(100, this.telemetry.battery.percent));
        document.getElementById('batteryLevel').style.width = batteryPercent + '%';
        document.getElementById('batteryVoltage').textContent = this.telemetry.battery.voltage.toFixed(1) + 'V';
        document.getElementById('batteryPercent').textContent = Math.round(batteryPercent) + '%';
        document.getElementById('batteryCurrent').textContent = this.telemetry.battery.current.toFixed(1) + 'A';
        
        if (batteryPercent < 20) {
            document.getElementById('batteryLevel').classList.add('low');
        } else {
            document.getElementById('batteryLevel').classList.remove('low');
        }
        
        // GPS
        const gpsStatuses = ['No Fix', '2D Fix', '3D Fix', 'DGPS', 'RTK'];
        document.getElementById('gpsStatus').textContent = gpsStatuses[this.telemetry.gps.fix] || 'No Fix';
        document.getElementById('gpsSats').textContent = this.telemetry.gps.satellites;
        document.getElementById('gpsHdop').textContent = this.telemetry.gps.hdop.toFixed(1);
        
        // HUD
        document.getElementById('hudLat').textContent = this.telemetry.gps.lat.toFixed(7);
        document.getElementById('hudLon').textContent = this.telemetry.gps.lon.toFixed(7);
        
        // Footer
        const hours = Math.floor(this.flightTime / 36000);
        const minutes = Math.floor((this.flightTime % 36000) / 600);
        const seconds = Math.floor((this.flightTime % 600) / 10);
        document.getElementById('flightTime').textContent = 
            `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        document.getElementById('distance').textContent = Math.round(this.telemetry.groundspeed * this.flightTime / 10) + ' m';
        document.getElementById('homeDistance').textContent = Math.round(Math.random() * 100) + ' m';
        document.getElementById('rcSignal').textContent = Math.round(this.telemetry.rcSignal) + '%';
        document.getElementById('telemetrySignal').textContent = Math.round(this.telemetry.telemetrySignal) + '%';
    }
    
    addMessage(text, type = 'info') {
        const messagesList = document.getElementById('messagesList');
        const message = document.createElement('div');
        message.className = `message ${type}`;
        message.textContent = `[${new Date().toLocaleTimeString()}] ${text}`;
        messagesList.insertBefore(message, messagesList.firstChild);
        
        // Keep only last 20 messages
        while (messagesList.children.length > 20) {
            messagesList.removeChild(messagesList.lastChild);
        }
    }
}

// Initialize application when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const app = new DroneGCS();
});
