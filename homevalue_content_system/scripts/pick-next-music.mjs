#!/usr/bin/env node
/** Pick next background music track — rotates through library, shuffles on cycle reset. */

import { readFileSync, writeFileSync, mkdirSync, existsSync, readdirSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const STATE_PATH = join(ROOT, 'content/state/music-rotation.json');
const MUSIC_DIR = join(ROOT, 'assets/music');
const TRACKS_CONFIG = join(ROOT, 'config/music-tracks.json');

function loadTracks() {
  if (existsSync(TRACKS_CONFIG)) {
    return JSON.parse(readFileSync(TRACKS_CONFIG, 'utf8'));
  }
  return readdirSync(MUSIC_DIR)
    .filter((f) => f.endsWith('.mp3'))
    .map((f) => ({ id: f.replace('.mp3', ''), name: f }));
}

function loadState() {
  if (!existsSync(STATE_PATH)) {
    return { usedIds: [], cycle: 0, lastPick: null };
  }
  return JSON.parse(readFileSync(STATE_PATH, 'utf8'));
}

function saveState(state) {
  mkdirSync(dirname(STATE_PATH), { recursive: true });
  writeFileSync(STATE_PATH, JSON.stringify(state, null, 2));
}

function shuffle(arr) {
  const a = [...arr];
  for (let i = a.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
}

export function pickNextMusic() {
  const tracks = loadTracks();
  const state = loadState();
  let pool = tracks.filter((t) => !state.usedIds.includes(t.id));

  if (pool.length === 0) {
    state.usedIds = [];
    state.cycle += 1;
    pool = shuffle(tracks);
  } else if (state.cycle === 0 && state.usedIds.length === 0) {
    pool = shuffle(pool);
  }

  const track = pool[0];
  const filePath = join(MUSIC_DIR, `${track.id}.mp3`);

  if (!existsSync(filePath)) {
    throw new Error(`Music file missing: ${filePath}. Run: node scripts/setup-music.mjs`);
  }

  state.usedIds.push(track.id);
  state.lastPick = { id: track.id, name: track.name, at: new Date().toISOString() };
  saveState(state);

  return { track, filePath };
}

if (process.argv[1]?.endsWith('pick-next-music.mjs')) {
  const { track, filePath } = pickNextMusic();
  console.log(JSON.stringify({ track, filePath }, null, 2));
}
