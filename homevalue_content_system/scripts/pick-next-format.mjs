#!/usr/bin/env node
/** Rotate through visual Reel formats so every post looks different. */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const STATE_PATH = join(ROOT, 'content/state/format-rotation.json');
const FORMATS = JSON.parse(readFileSync(join(ROOT, 'config/video-formats.json'), 'utf8'));

function loadState() {
  if (!existsSync(STATE_PATH)) return { usedIds: [], cycle: 0 };
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

export function pickNextFormat() {
  const state = loadState();
  let pool = FORMATS.filter((f) => !state.usedIds.includes(f.id));

  if (pool.length === 0) {
    state.usedIds = [];
    state.cycle += 1;
    pool = shuffle(FORMATS);
  } else if (state.usedIds.length === 0) {
    pool = shuffle(pool);
  }

  const format = pool[0];
  state.usedIds.push(format.id);
  state.lastPick = { id: format.id, name: format.name, at: new Date().toISOString() };
  saveState(state);
  return format;
}

if (process.argv[1]?.endsWith('pick-next-format.mjs')) {
  console.log(JSON.stringify(pickNextFormat(), null, 2));
}
