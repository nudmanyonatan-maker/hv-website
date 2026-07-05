#!/usr/bin/env node
/** Upload media to public HTTPS host (IG-safe, no query params). */

import { readFileSync } from 'fs';
import { basename, extname } from 'path';

async function uploadFile(filePath, mimeType) {
  const fileBuffer = readFileSync(filePath);
  const blob = new Blob([fileBuffer], { type: mimeType });
  const form = new FormData();
  form.append('file', blob, basename(filePath));

  const res = await fetch('https://tmpfiles.org/api/v1/upload', { method: 'POST', body: form });
  const json = await res.json();

  if (json.status !== 'success' || !json.data?.url) {
    throw new Error(`Upload failed: ${JSON.stringify(json)}`);
  }

  const viewUrl = json.data.url;
  return viewUrl.replace('tmpfiles.org/', 'tmpfiles.org/dl/');
}

export async function uploadVideo(filePath) {
  return uploadFile(filePath, 'video/mp4');
}

export async function uploadImage(filePath) {
  const ext = extname(filePath).toLowerCase();
  const mime = ext === '.png' ? 'image/png' : 'image/jpeg';
  return uploadFile(filePath, mime);
}

/** @deprecated use uploadVideo */
export const uploadToCatbox = uploadVideo;

if (process.argv[1]?.endsWith('upload-catbox.mjs')) {
  const file = process.argv[2];
  if (!file) {
    console.error('Usage: node scripts/upload-catbox.mjs <file>');
    process.exit(1);
  }
  const ext = extname(file).toLowerCase();
  const url = ext === '.mp4'
    ? await uploadVideo(file)
    : await uploadImage(file);
  console.log(url);
}
