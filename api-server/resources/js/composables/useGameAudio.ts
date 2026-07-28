import { ref } from 'vue';
import type { SoundType } from '@/types/adventurer';

const audioEnabled = ref(true);
let audioCtx: AudioContext | null = null;

export function useGameAudio() {
    const playSound = (type: SoundType): void => {
        if (!audioEnabled.value) {
            return;
        }

        try {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) {
                return;
            }

            if (!audioCtx) {
                audioCtx = new AudioContextClass();
            }

            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);

            if (type === 'click') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                osc.frequency.exponentialRampToValueAtTime(1200, audioCtx.currentTime + 0.1);
                gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gain.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.1);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.1);
                return;
            }

            if (type === 'level-up') {
                const now = audioCtx.currentTime;
                const notes = [523.25, 659.25, 783.99, 1046.5];

                notes.forEach((freq, idx) => {
                    const noteOsc = audioCtx!.createOscillator();
                    const noteGain = audioCtx!.createGain();
                    noteOsc.connect(noteGain);
                    noteGain.connect(audioCtx!.destination);
                    noteOsc.type = 'triangle';
                    noteOsc.frequency.setValueAtTime(freq, now + idx * 0.12);
                    noteGain.gain.setValueAtTime(0.15, now + idx * 0.12);
                    noteGain.gain.linearRampToValueAtTime(0, now + idx * 0.12 + 0.3);
                    noteOsc.start(now + idx * 0.12);
                    noteOsc.stop(now + idx * 0.12 + 0.3);
                });
                return;
            }

            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(300, audioCtx.currentTime);
            osc.frequency.linearRampToValueAtTime(150, audioCtx.currentTime + 0.15);
            gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
            gain.gain.linearRampToValueAtTime(0, audioCtx.currentTime + 0.15);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.15);
        } catch (error) {
            console.log('Audio Web API Error:', error);
        }
    };

    const toggleAudio = (): void => {
        audioEnabled.value = !audioEnabled.value;
        if (audioEnabled.value) {
            playSound('click');
        }
    };

    return {
        audioEnabled,
        playSound,
        toggleAudio,
    };
}

declare global {
    interface Window {
        webkitAudioContext?: typeof AudioContext;
    }
}
