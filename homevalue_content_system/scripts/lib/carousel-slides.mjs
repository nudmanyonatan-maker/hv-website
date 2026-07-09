/**
 * Instagram carousel slide templates — 1080×1350 (4:5).
 * Product slides use shared hero layout (full product visible).
 */

export {
  renderProductHeroSlide as renderProductSlide,
  renderCoverSlide,
  renderCtaSlide,
  FEED_W,
  FEED_H,
} from './product-slide.mjs';

export function styleForIndex() {
  return 'hero';
}

export const SLIDE_STYLES = ['hero'];
