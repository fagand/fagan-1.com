#!/usr/bin/env bash
# =====================================================================
# build.sh — motivation clip pipeline  |  fagan-1.com
#
# WHAT IT DOES
#   1. Finds any "source" video in this folder (anything that isn't
#      already a motiv-NN.mp4 / motiv-NN.jpg).
#   2. Encodes each to the next free motiv-NN slot: web-optimised
#      720/960-max H.264 MP4 + a poster JPEG.
#   3. Deletes the original source.
#   4. Rewrites the playlist inside ../../motivation.html (the block
#      between the BUILD:CLIPS markers) from every motiv-*.mp4 present.
#
# USAGE
#   Drop a video into assets/vids/  then run:
#       ./assets/vids/build.sh
#   Re-running with no new sources just re-syncs the playlist (safe).
#
# Requires: ffmpeg + ffprobe on PATH.
# =====================================================================
set -euo pipefail

VIDS_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
HTML="$(cd "$VIDS_DIR/../.." && pwd)/motivation.html"
SCALE="scale='trunc(iw*min(1,960/max(iw,ih))/2)*2':'trunc(ih*min(1,960/max(iw,ih))/2)*2'"

cd "$VIDS_DIR"

# ---- next free motiv-NN index -------------------------------------
next_index() {
  local n=1
  while [ -f "$(printf 'motiv-%02d.mp4' "$n")" ]; do n=$((n + 1)); done
  printf '%02d' "$n"
}

# ---- encode any source videos -------------------------------------
shopt -s nullglob nocaseglob
encoded_any=0
for f in *; do
  case "$f" in
    motiv-[0-9][0-9].mp4|motiv-[0-9][0-9].jpg) continue ;;   # already ours
    build.sh|.*|*.md)                          continue ;;   # skip script/hidden/docs
  esac
  # must be a video ffprobe can read
  if ! ffprobe -v error -select_streams v:0 -show_entries stream=codec_name \
       -of csv=p=0 "$f" >/dev/null 2>&1; then
    echo "skip (not a video): $f"; continue
  fi

  idx="$(next_index)"
  out="motiv-$idx"
  echo "encoding  $f  ->  $out.mp4"
  ffmpeg -y -v error -i "$f" -vf "$SCALE" \
    -c:v libx264 -crf 25 -preset medium -profile:v high -pix_fmt yuv420p \
    -c:a aac -b:a 128k -movflags +faststart "$out.mp4"
  ffmpeg -y -v error -ss 1 -i "$out.mp4" -frames:v 1 -q:v 4 "$out.jpg"
  rm -f "$f"
  encoded_any=1
done
shopt -u nullglob nocaseglob

# ---- regenerate the playlist block in motivation.html -------------
# Build the JS lines from every motiv-NN.mp4 on disk, in order, into a
# temp file (awk reads it via getline — avoids newline-in-var issues).
block="$(mktemp)"
mp4s=()
while IFS= read -r line; do mp4s+=("$line"); done < <(ls motiv-[0-9][0-9].mp4 2>/dev/null | sort)
for i in "${!mp4s[@]}"; do
  base="${mp4s[$i]%.mp4}"
  comma=","; [ "$i" -eq $(( ${#mp4s[@]} - 1 )) ] && comma=""   # last line: no comma
  printf "            { src: '/assets/vids/%s.mp4', poster: '/assets/vids/%s.jpg' }%s\n" \
    "$base" "$base" "$comma" >> "$block"
done

if [ -f "$HTML" ]; then
  tmp="$(mktemp)"
  awk -v blockfile="$block" '
    /BUILD:CLIPS:START/ { print; while ((getline l < blockfile) > 0) print l; close(blockfile); skip=1; next }
    /BUILD:CLIPS:END/   { skip=0 }
    !skip               { print }
  ' "$HTML" > "$tmp" && mv "$tmp" "$HTML"
  echo "playlist synced in motivation.html"
else
  echo "WARN: $HTML not found — playlist not updated"
fi
rm -f "$block"

count=$(ls motiv-[0-9][0-9].mp4 2>/dev/null | wc -l | tr -d ' ')
echo "done — $count clip(s) in the playlist."
[ "$encoded_any" = 0 ] && echo "(no new source videos found; just re-synced.)"
