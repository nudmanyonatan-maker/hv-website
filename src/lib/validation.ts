import { z } from 'zod';

export const loginSchema = z.object({
  email: z.string().email('Invalid email'),
  password: z.string().min(1, 'Password is required'),
});

export const registerSchema = z.object({
  contactName: z.string().min(1, 'Name is required').max(200),
  companyName: z.string().min(1, 'Company name is required').max(200),
  taxId: z.string().max(50).optional().default(''),
  address: z.string().max(500).optional().default(''),
  email: z.string().email('Invalid email'),
  phone: z.string().max(30).optional().default(''),
  mobile: z.string().max(30).optional().default(''),
  hasWhatsapp: z.boolean().optional().default(false),
  password: z.string().min(6, 'Password must be at least 6 characters'),
});

export const contactSchema = z.object({
  name: z.string().min(1).max(200),
  email: z.string().email(),
  subject: z.string().min(1).max(300),
  message: z.string().min(1).max(5000),
});

export const cartAddSchema = z.object({
  productId: z.coerce.number().int().positive(),
  qty: z.coerce.number().int().positive(),
});

export const cartRemoveSchema = z.object({
  cartId: z.number().int().positive(),
});

export const orderSchema = z.object({
  orderComment: z.string().max(1000).optional().default(''),
  items: z.array(z.object({
    product_id: z.number().int().positive(),
    qty: z.number().int().positive(),
    sale_price: z.number().nonnegative(),
  })).min(1, 'At least one item required'),
  lang: z.string().optional(),
});
