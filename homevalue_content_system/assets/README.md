# Background Music Library

12 rotating tracks — **a different song every Reel** until the full library cycles, then shuffles and repeats.

## Setup (one-time)

```bash
npm run setup:music
```

Downloads royalty-free clips from [SoundHelix](https://www.soundhelix.com/) (Creative Commons), trims to ~14s each with fade in/out.

## How rotation works

- `pick-next-music.mjs` picks the next unused track from `config/music-tracks.json`
- State saved in `content/state/music-rotation.json`
- When all 12 are used, pool shuffles and starts fresh — never the same track twice in a row within a cycle

## Add or swap tracks

Edit `config/music-tracks.json` — each entry needs:
- `id` — filename without extension
- `name` — label for logs
- `source` — URL to royalty-free MP3
- `startSec` — where to clip from (adds variety within long songs)

Then run `npm run setup:music` again.
