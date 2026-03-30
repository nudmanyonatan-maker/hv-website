import type { Metadata } from 'next';
import Link from 'next/link';
import type { Locale } from '@/types';
import { getDictionary } from '@/i18n/getDictionary';
import { getSession } from '@/lib/auth';
import { getProductDetails } from '@/lib/fullvendor';
import { formatPrice, getProductImage, parseTags, langToId } from '@/lib/utils';
import ImageGallery from '@/components/products/ImageGallery';
import AddToCartButton from '@/components/products/AddToCartButton';

const SITE_URL = 'https://hv-website-phi.vercel.app';

export async function generateMetadata({
  params,
}: {
  params: Promise<{ lang: string; id: string }>;
}): Promise<Metadata> {
  const { lang, id } = await params;
  const locale = (lang === 'es' ? 'es' : 'en') as Locale;
  const res = await getProductDetails(Number(id), langToId(locale));
  const product = res.details;

  if (!product) {
    return { title: 'Product not found | Home Value' };
  }

  const description = product.descriptions
    ? product.descriptions.substring(0, 160)
    : '';
  const mainImage = getProductImage(product);

  return {
    title: `${product.name} | Home Value`,
    description,
    openGraph: {
      title: `${product.name} | Home Value`,
      description,
      type: 'article',
      images: [mainImage],
    },
  };
}

export default async function ProductDetailPage({
  params,
}: {
  params: Promise<{ lang: string; id: string }>;
}) {
  const { lang, id } = await params;
  const locale = (lang === 'es' ? 'es' : 'en') as Locale;
  const dict = await getDictionary(locale);
  const session = await getSession();
  const isAuthenticated = !!session?.approved;

  const res = await getProductDetails(Number(id), langToId(locale));
  const product = res.details;

  if (!product) {
    return (
      <div className="max-w-7xl mx-auto px-4 py-20 text-center">
        <h1 className="text-2xl font-bold text-gray-900">{dict.product.product_not_found}</h1>
        <Link href={`/${locale}`} className="text-red-700 mt-4 inline-block">
          {dict.common.back}
        </Link>
      </div>
    );
  }

  const mainImage = getProductImage(product);
  const tags = parseTags(product.tags);
  const images = product.images ?? [];

  const productJsonLd = {
    '@context': 'https://schema.org',
    '@type': 'Product',
    name: product.name,
    description: product.descriptions || '',
    sku: product.sku,
    image: mainImage,
    url: `${SITE_URL}/${locale}/products/${product.product_id}`,
    offers: {
      '@type': 'Offer',
      price: product.sale_price,
      priceCurrency: product.currency_type || 'USD',
      availability: product.available_stock > 0
        ? 'https://schema.org/InStock'
        : 'https://schema.org/OutOfStock',
    },
  };

  return (
    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(productJsonLd) }}
      />
      <Link href={`/${locale}`} className="text-sm text-gray-500 hover:text-gray-700 mb-6 inline-block">
        &larr; {dict.common.back}
      </Link>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-10">
        {/* Images */}
        {images.length > 0 ? (
          <ImageGallery images={images} alt={product.name} />
        ) : (
          <div className="relative aspect-square bg-gray-50 rounded-xl" />
        )}

        {/* Info */}
        <div>
          <h1 className="text-2xl font-bold text-gray-900 mb-2">{product.name}</h1>

          <p className="text-sm text-gray-400 mb-4">
            {dict.product.sku}: {product.sku}
            {product.barcode && ` | ${product.barcode}`}
          </p>

          {/* Price */}
          <div className="mb-6 p-4 bg-gray-50 rounded-xl">
            {isAuthenticated ? (
              <div>
                <span className="text-3xl font-bold text-gray-900">
                  {formatPrice(product.sale_price, product.currency_type, locale)}
                </span>
                <span className="text-sm text-gray-400 ml-2">/ {product.unit_type}</span>
              </div>
            ) : (
              <p className="text-red-700 font-medium">
                {dict.product.login_to_see_prices}
              </p>
            )}
          </div>

          {/* Stock */}
          <div className="mb-4">
            {product.available_stock > 0 ? (
              <span className="text-sm text-green-600 font-medium">
                {dict.product.in_stock} ({product.available_stock} {product.unit_type})
              </span>
            ) : (
              <span className="text-sm text-red-500 font-medium">
                {dict.product.out_of_stock}
              </span>
            )}
          </div>

          {/* Description */}
          {product.descriptions && (
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-1">{dict.product.description}</h3>
              <p className="text-sm text-gray-600 leading-relaxed">{product.descriptions}</p>
            </div>
          )}

          {/* Tags */}
          {tags.length > 0 && (
            <div className="mb-6">
              <h3 className="text-sm font-semibold text-gray-900 mb-2">{dict.product.tags}</h3>
              <div className="flex flex-wrap gap-2">
                {tags.map((tag) => (
                  <span key={tag} className="text-xs bg-gray-100 text-gray-600 px-3 py-1 rounded-full">
                    {tag}
                  </span>
                ))}
              </div>
            </div>
          )}

          {/* Add to cart — works for all users (guest cart stored in localStorage) */}
          <AddToCartButton
            productId={product.product_id}
            name={product.name}
            sku={product.sku}
            image={mainImage}
            label={dict.product.add_to_cart}
            lang={locale}
            moq={product.minimum_stock}
            unitType={product.unit_type}
          />
        </div>
      </div>
    </div>
  );
}
