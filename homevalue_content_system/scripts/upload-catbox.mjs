#!/usr/bin/env node
/** Upload video to public HTTPS host (IG-safe, no query params). */

import { readFileSync } from 'fs';
import { basename } from 'path';

export async function uploadVideo(filePath) {
  // Primary: tmpfiles.org (catbox is currently disabled)
  const fileBuffer = readFileSync(filePath);
  const blob = new Blob([fileBuffer], { type: 'video/mp4' });
  const form = new FormData();
  form.append('file', blob, basename(filePath));

  const res = await fetch('https://tmpfiles.org/api/v1/upload', { method: 'POST', body: form });
  const json = await res.json();

  if (json.status !== 'success' || !json.data?.url) {
    throw new Error(`Upload failed: ${JSON.stringify(json)}`);
  }

  // Convert view URL → direct download URL (required for Instagram fetch)
  const viewUrl = json.data.url;
  const directUrl = viewUrl.replace('tmpfiles.org/', 'tmpfiles.org/dl/');
  return directUrl;
}

/** @deprecated use uploadVideo */
export const uploadToCatbox = uploadVideo;

if (process.argv[1]?.endsWith('upload-catbox.mjs')) {
  const file = process.argv[2];
  if (!file) {
    console.error('Usage: node scripts/upload-catbox.mjs <file.mp4>');
    process.exit(1);
  }
  const url = await uploadVideo(file);
  console.log(url);
}
