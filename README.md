# abc-music-player-en

English version of the abcMusic song delivery page (Spotify-style player).
Port of [`abc-music-player`](https://github.com/chnproject1/abc-music-player).

## Route

```
https://<domain>/{uuid}
```

`.htaccess` rewrites every non-file path to `index.php`, which reads the 36-char
UUID from the path and looks the record up in Supabase.

## Data source

| Item | Value |
|---|---|
| Supabase project | `baltzukuszagxcgkfrpi` |
| Table | `presentes` (constant `SUPABASE_TABLE` in `index.php`) |
| Lookup | `uuid=eq.{uuid}&limit=1` |
| Fields used | `titulo`, `audio_url`, `cover_url` |

If the US operation ever gets its own table, change `SUPABASE_TABLE` — that is the
only place the table name appears.

## Differences from the PT-BR version

- `lang="en"`, all UI copy, `aria-label`s, OG tags and code comments in English
- Default title `Your special song` (was `Sua música especial`)
- Table name extracted into the `SUPABASE_TABLE` constant
- Track title is passed to JS via `json_encode` instead of `addslashes`
- `shareMusic()` takes the event explicitly instead of relying on the global `event`
- Added `aria-label` to the volume slider
- The footer is now a **CTA** — `Loved it? Make another one at abcMusic`, pointing to
  `https://abcmusic-quiz-us.netlify.app/?utm_source=link_pagina_entrega`
  (was `Made with ♥ by abcMusic` → `abcmusic.tech`). Brightened from `#535353` to
  `#b3b3b3` with a hover state, so it reads as a call to action instead of a credit line.

Everything else is unchanged: cover + blur backdrop, play/pause, ±10s seek,
scrubbable progress bar, volume, shuffle, repeat, like and native share.

## Deploy

```bash
docker build -t abc-music-player-en . && docker run -p 8080:80 abc-music-player-en
```

Then open `http://localhost:8080/<uuid>`.
