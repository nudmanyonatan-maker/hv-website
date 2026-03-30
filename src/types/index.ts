// FullVendor API Types

export interface ProductImage {
  product_id: number;
  company_id: number;
  img_id: number;
  pic: string;
  local: string;
  img_order?: number;
  img_default?: number;
}

export interface Product {
  product_id: number;
  name: string;
  sku: string;
  category_id: string;
  barcode: string;
  descriptions: string;
  unit_type: string;
  stock: number;
  lblstock: string;
  total_order: number;
  available_stock: number;
  minimum_stock: number;
  force_moq: number;
  sale_price: number;
  sale_price0: number;
  fob_price: number;
  purchase_price: number;
  currency_type: string;
  tags: string;
  status: number;
  catalog_id: number;
  catalog_order: number;
  images: ProductImage[];
  requested: { customer_id: number; qty: number; requested: number }[];
}

/** Product with price fields stripped — for public visitors */
export type PublicProduct = Omit<
  Product,
  'sale_price' | 'sale_price0' | 'fob_price' | 'purchase_price'
>;

export interface Category {
  category_id: number;
  cat_id: number;
  category_name: string;
  order_id: number;
  company_id: number;
  category_status: number;
  category_created_at: string;
  id_kor: string;
  images: string;
}

export interface Customer {
  customer_id: number;
  company_id: number;
  user_id: number;
  name: string;
  business_name: string;
  tax_id: string;
  email: string;
  phone: string;
  cell_phone: string;
  notes: string;
  discount: number;
  term_id: number;
  group_id: number;
  group_name: string;
  percentage_on_price: number;
  percent_price_amount: number;
  commercial_address: string;
  commercial_city: string;
  commercial_state: string;
  commercial_country: string;
  commercial_zip_code: string;
  dispatch_address: string;
  dispatch_city: string;
  dispatch_state: string;
  dispatch_country: string;
  dispatch_zip_code: string;
  catalog_emails: number;
  customer_status: number;
  customer_created_at: string;
  cust_id_kor: string;
  id_kor: string;
}

export interface UserInfo {
  user_id: number;
  unique_id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone_number: string;
  profile_image: string;
  company_image: string;
  profile: string;
  company_id: number;
  company_name: string;
  show_inventory: number;
  language_id: number;
  order_discount: number;
  order_net_discount: number;
  can_change_price: number;
  can_send_catalog: number;
  can_create_customer: number;
}

export interface OrderLineItem {
  order_id: number;
  product_id: number;
  name: string;
  sku: string;
  barcode: string;
  qty: number;
  sale_price: number;
  fob_price: number;
  purchase_price: number;
  discount: number;
  discount_type: string;
  comment: string;
  created: string;
  currency_type: string;
}

export interface Order {
  order_id: number;
  order_number: string;
  order_comments: string;
  customer_id: number;
  name: string;
  business_name: string;
  email: string;
  phone: string;
  amount: number;
  ordered_total: number;
  total_amount: number;
  discount: number;
  discount_type: string;
  discount_a: number;
  created: string;
  updated: string;
  name_status_spanish: string;
  name_status_english: string;
  scolor: string;
  warehouse_user_id: number;
  warehouse_assign_date: string;
  warehouse_name: string;
  tipo_d: string;
  product_list: OrderLineItem[];
}

export interface CartItem {
  cart_id: number;
  product_id: number;
  name: string;
  sku: string;
  qty: number;
  sale_price: number;
  discount: number;
  discount_type: string;
  comments: string;
  images: ProductImage[];
}

/** Fields removed by slimProducts() for catalog card display */
type SlimOmitFields = 'requested' | 'barcode' | 'stock' | 'total_order' | 'force_moq' | 'catalog_id' | 'lblstock';

/** Product slimmed for catalog cards — heavy fields stripped */
export type SlimProduct = Omit<Product, SlimOmitFields>;

/** Public product slimmed for catalog cards */
export type SlimPublicProduct = Omit<PublicProduct, SlimOmitFields>;

export interface FullVendorResponse<T = unknown> {
  status: string | boolean;
  message?: string;
  error?: string;
  list?: T[];
  info?: T;
  details?: T;
  order_list?: T[];
}

// Session / Auth types
export interface SessionPayload {
  userId: number;
  companyId: number;
  customerId: number;
  email: string;
  name: string;
  approved: boolean;
}

// Registration
export interface PendingRegistration {
  id: number;
  contact_name: string;
  company_name: string;
  tax_id: string;
  address: string;
  email: string;
  phone: string;
  mobile: string;
  has_whatsapp: boolean;
  password_hash: string;
  status: 'pending' | 'approved' | 'rejected';
  fullvendor_customer_id: number | null;
  created_at: string;
}

// i18n
export type Locale = 'en' | 'es';

export interface Dictionary {
  nav: {
    home: string;
    catalog: string;
    contact: string;
    blog: string;
    login: string;
    register: string;
    cart: string;
    orders: string;
    invoices: string;
    account: string;
    logout: string;
    admin: string;
  };
  home: {
    hero_title: string;
    hero_subtitle: string;
    all_categories: string;
    search_placeholder: string;
    login_to_see_prices: string;
    add_to_cart: string;
    view_details: string;
    no_products: string;
    products_found: string;
    clear_filters: string;
    continue_shopping: string;
    load_more: string;
    showing_of: string;
    all_products: string;
    in_stock: string;
    out_of_stock_badge: string;
  };
  product: {
    sku: string;
    description: string;
    tags: string;
    unit: string;
    stock: string;
    in_stock: string;
    out_of_stock: string;
    price: string;
    add_to_cart: string;
    login_to_see_prices: string;
    related_products: string;
    product_not_found: string;
  };
  auth: {
    login_title: string;
    register_title: string;
    email: string;
    password: string;
    login_button: string;
    register_button: string;
    no_account: string;
    has_account: string;
    contact_name: string;
    company_name: string;
    tax_id: string;
    address: string;
    phone: string;
    mobile: string;
    has_whatsapp: string;
    registration_success: string;
    pending_approval: string;
    invalid_credentials: string;
    forgot_password: string;
  };
  cart: {
    title: string;
    empty: string;
    continue_shopping: string;
    confirm_clear: string;
    quantity: string;
    total: string;
    subtotal: string;
    remove: string;
    clear_all: string;
    send_order: string;
    pay_now: string;
    order_comments: string;
    order_success: string;
    quantity_label: string;
  };
  orders: {
    title: string;
    order_number: string;
    date: string;
    status: string;
    total: string;
    no_orders: string;
    details: string;
  };
  contact: {
    title: string;
    name: string;
    email: string;
    subject: string;
    message: string;
    send: string;
    success: string;
    phone_label: string;
    email_label: string;
  };
  common: {
    loading: string;
    error: string;
    back: string;
    save: string;
    cancel: string;
    confirm: string;
    yes: string;
    no: string;
    page_not_found: string;
    return_home: string;
    try_again: string;
    something_went_wrong: string;
    go_home: string;
  };
  admin: {
    title: string;
    no_registrations: string;
    tax_id: string;
    address: string;
    whatsapp: string;
    registered: string;
    approve: string;
    reject: string;
    approved: string;
    rejected: string;
  };
  invoices: {
    title: string;
    no_invoices: string;
    invoice: string;
    date: string;
    amount: string;
    status: string;
  };
  footer: {
    links: string;
    social: string;
    contact: string;
    rights: string;
  };
  table: {
    product: string;
    sku: string;
    qty: string;
    price: string;
    line_total: string;
  };
  toast: {
    added_to_cart: string;
    order_placed: string;
    message_sent: string;
    item_removed: string;
    cart_cleared: string;
    error_generic: string;
  };
  breadcrumb: {
    home: string;
    products: string;
  };
  seo: {
    title: string;
    description: string;
  };
}
