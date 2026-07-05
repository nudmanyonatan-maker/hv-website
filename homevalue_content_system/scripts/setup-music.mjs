#!/usr/bin/env node
/**
 * Download and prepare rotating background music library.
 * Usage: node scripts/setup-music.mjs
 */

import { readFileSync, writeFileSync, mkdirSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';
import { execSync } from 'child_process';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');
const schedule = JSON.parse(readFileSync(join(ROOT, 'config/schedule.json'), 'utf8'));
const tracks = JSON.parse(readFileSync(join(ROOT, 'config/music-tracks.json'), 'utf8'));
const musicDir = join(ROOT, 'assets/music');
const clipDur = (schedule.reelDurationSec || 12) + 2;

mkdirSync(musicDir, { recursive: true });

for (const track of tracks) {
  const outPath = join(musicDir, `${track.id}.mp3`);
  if (existsSync(outPath)) {
    console.log(`✓ ${track.id} exists`);
    continue;
  }

  const tmpFull = join(musicDir, `${track.id}-full.mp3`);
  console.log(`Downloading ${track.name}...`);
  execSync(`curl -sL -o "${tmpFull}" "${track.source}"`, { stdio: 'inherit' });

  const start = track.startSec || 0;
  execSync(
    `ffmpeg -y -ss ${start} -i "${tmpFull}" -t ${clipDur} ` +
    `-af "volume=0.35,afade=t=in:st=0:d=0.5,afade=t=out:st=${clipDur - 1.5}:d=1.5" ` +
    `-b:a 128k "${outPath}"`,
    { stdio: 'pipe' }
  );
  try { execSync(`rm "${tmpFull}"`); } catch { /* ok */ }
  console.log(`✓ ${track.id} ready`);
}

writeFileSync(
  join(musicDir, 'catalog.json'),
  JSON.stringify({ generatedAt: new Date().toISOString(), tracks: tracks.map((t) => t.id) }, null, 2)
);
console.log(`\n✓ ${tracks.length} tracks in assets/music/`);
