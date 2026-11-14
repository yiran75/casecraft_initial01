/**
 * CaseCraft - Cyber Sound Synthesizer (Web Audio API)
 * Generates suspenseful UI sound effects dynamically without external audio assets.
 */

class CyberAudio {
    constructor() {
        this.ctx = null;
        this.muted = localStorage.getItem('cyber_audio_muted') === 'true';
    }

    init() {
        if (!this.ctx) {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (AudioCtx) {
                this.ctx = new AudioCtx();
            }
        }
    }

    toggleMute() {
        this.muted = !this.muted;
        localStorage.setItem('cyber_audio_muted', this.muted);
        return this.muted;
    }

    playTone(freq, type = 'sine', duration = 0.08, gainVal = 0.05) {
        if (this.muted) return;
        try {
            this.init();
            if (!this.ctx) return;
            if (this.ctx.state === 'suspended') {
                this.ctx.resume();
            }

            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = type;
            osc.frequency.setValueAtTime(freq, this.ctx.currentTime);

            gain.gain.setValueAtTime(gainVal, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, this.ctx.currentTime + duration);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start();
            osc.stop(this.ctx.currentTime + duration);
        } catch (e) {
            // Audio API restricted before user interaction
        }
    }

    // High-tech tactile click
    click() {
        this.playTone(1200, 'triangle', 0.04, 0.04);
    }

    // Biometric scanner laser hum
    scan() {
        if (this.muted) return;
        try {
            this.init();
            if (!this.ctx) return;
            const osc = this.ctx.createOscillator();
            const gain = this.ctx.createGain();

            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(350, this.ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(750, this.ctx.currentTime + 1.2);

            gain.gain.setValueAtTime(0.04, this.ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 1.2);

            osc.connect(gain);
            gain.connect(this.ctx.destination);

            osc.start();
            osc.stop(this.ctx.currentTime + 1.2);
        } catch(e) {}
    }

    // Access Granted Chime
    accessGranted() {
        if (this.muted) return;
        setTimeout(() => this.playTone(523.25, 'sine', 0.12, 0.06), 0);
        setTimeout(() => this.playTone(659.25, 'sine', 0.12, 0.06), 100);
        setTimeout(() => this.playTone(783.99, 'sine', 0.18, 0.07), 200);
        setTimeout(() => this.playTone(1046.50, 'sine', 0.25, 0.08), 300);
    }

    // Access Denied / Threat Warning
    accessDenied() {
        if (this.muted) return;
        this.playTone(160, 'sawtooth', 0.25, 0.08);
        setTimeout(() => this.playTone(130, 'sawtooth', 0.35, 0.08), 150);
    }
}

window.cyberSound = new CyberAudio();

// Auto-bind sound effects to interactive buttons
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('button, a.btn, .form-check-input, .nav-link').forEach(el => {
        el.addEventListener('click', () => {
            window.cyberSound.click();
        });
    });
});
