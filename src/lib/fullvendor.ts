import type {
  Product,
  Category,
  CartItem,
  Order,
  UserInfo,
  FullVendorResponse,
} from '@/types';
import { env } from './env';

const BASE_URL = env.FULLVENDOR_BASE_URL;
const TOKEN = env.FULLVENDOR_TOKEN;
const COMPANY_ID = env.FULLVENDOR_COMPANY_ID;

const API_TIMEOUT_MS = 90_000;

async function post<T>(endpoint: string, data: Record<string, unknown> = {}, cache?: { revalidate: number }): Promise<T> {
  const url = `${BASE_URL}${endpoint}`;
  const body = JSON.stringify({ company_id: COMPANY_ID, ...data });

  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), API_TIMEOUT_MS);

  try {
    const fetchOptions: RequestInit & { next?: { revalidate: number } } = {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-API-KEY': TOKEN },
      body,
      signal: controller.signal,
    };
    if (cache) fetchOptions.next = cache;

    const res = await fetch(url, fetchOptions);

    if (!res.ok) {
      const text = await res.text().catch(() => '(empty body)');
      throw new Error(`FullVendor API HTTP ${res.status} on ${endpoint}: ${text.slice(0, 200)}`);
    }

    const json = await res.json();

    if (json.status === false && json.error) {
      throw new Error(`FullVendor API error: ${json.error}`);
    }
    return json;
  } catch (error) {
    if (error instanceof Error && error.name === 'AbortError') {
      throw new Error(`FullVendor API timeout: ${endpoint} did not respond within ${API_TIMEOUT_MS / 1000}s`);
    }
    throw error;
  } finally {
    clearTimeout(timeoutId);
  }
}

// ─── Products ────────────────────────────────────────

export async function getProducts(opts?: {
  categoryId?: string;
  customerId?: string;
  languageId?: string;
}) {
  const data: Record<string, unknown> = {
    language_id: opts?.languageId ?? '1',
  };
  if (opts?.categoryId) data.category_id = opts.categoryId;
  if (opts?.customerId) data.customer_id = opts.customerId;

  // No cache here — response is >2MB which exceeds Next.js data cache limit.
  // Use catalog-cache.ts for in-memory caching instead.
  return post<FullVendorResponse<Product>>('productList', data);
}

export async function getProductDetails(productId: number, languageId = '1') {
  // Single product details are small enough for Next.js cache
  return post<FullVendorResponse<Product>>('productDetails', {
    product_id: productId,
    language_id: languageId,
  }, { revalidate: 300 });
}

// ─── Categories ──────────────────────────────────────

export async function getCategories(languageId = '1') {
  // Small response, but cached via catalog-cache.ts for consistency
  return post<FullVendorResponse<Category>>('categoryList', {
    language_id: languageId,
  });
}

// ─── Auth ────────────────────────────────────────────

export async function login(email: string, password: string) {
  return post<{
    status: string;
    user_type: string;
    info: UserInfo;
    error?: string;
  }>('login', {
    email,
    password,
    user_type: 1,
  });
}

// ─── Customers ───────────────────────────────────────

export async function createCustomer(data: {
  userId: number;
  languageId?: string;
  businessName: string;
  name: string;
  taxId?: string;
  email?: string;
  phone?: string;
  cellPhone?: string;
  termId?: number;
  groupId?: number;
  commercialAddress?: string;
}) {
  return post<FullVendorResponse>('createCustomer', {
    user_id: data.userId,
    language_id: data.languageId ?? '1',
    business_name: data.businessName,
    name: data.name,
    tax_id: data.taxId ?? '',
    email: data.email ?? '',
    phone: data.phone ?? '',
    cell_phone: data.cellPhone ?? '',
    term_id: data.termId ?? 1,
    group_id: data.groupId ?? 1,
    commercial_address: data.commercialAddress ?? '',
  });
}

export async function getCustomers() {
  return post<FullVendorResponse<unknown>>('customersList');
}

// ─── Cart ────────────────────────────────────────────

export async function getCart(userId: number, languageId = '1') {
  return post<FullVendorResponse<CartItem>>('cartList', {
    user_id: userId,
    language_id: languageId,
  });
}

export async function addToCart(data: {
  userId: number;
  productId: number;
  qty: number;
  languageId?: string;
}) {
  return post<FullVendorResponse>('addEditCart', {
    user_id: data.userId,
    language_id: data.languageId ?? '1',
    product_id: data.productId,
    qty: data.qty,
  });
}

export async function removeFromCart(cartId: number) {
  return post<FullVendorResponse>('deleteCart', { cart_id: cartId });
}

export async function clearCart(userId: number) {
  return post<FullVendorResponse>('deleteCartAll', { user_id: userId });
}

// ─── Orders ──────────────────────────────────────────

export async function createOrder(data: {
  userId: number;
  customerId: number;
  languageId?: string;
  discount?: number;
  discountType?: string;
  orderComment?: string;
  items: { product_id: number; qty: number; sale_price: number }[];
}) {
  return post<FullVendorResponse & { order_id?: number }>('addOrder', {
    user_id: data.userId,
    language_id: data.languageId ?? '1',
    customer_id: data.customerId,
    uuid: crypto.randomUUID(),
    discount: data.discount ?? 0,
    discount_type: data.discountType ?? 'amount',
    order_comment: data.orderComment ?? '',
    itemList: data.items,
  });
}

export async function getOrders(userId: number, customerId: number, languageId = '1') {
  return post<{ status: string; order_list: Order[] }>('orderList', {
    user_id: userId,
    customer_id: customerId,
    language_id: languageId,
  });
}

// ─── Terms of Sales ──────────────────────────────────

export async function getTermsOfSales(languageId = '1') {
  return post<FullVendorResponse>('termsOfSalesList', {
    language_id: languageId,
  }, { revalidate: 60 });
}
