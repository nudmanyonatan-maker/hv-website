-- Run this in your Vercel Postgres dashboard or via CLI
-- Only needed once to set up the database

CREATE TABLE IF NOT EXISTS pending_registrations (
  id SERIAL PRIMARY KEY,
  contact_name TEXT NOT NULL,
  company_name TEXT NOT NULL,
  tax_id TEXT DEFAULT '',
  address TEXT DEFAULT '',
  email TEXT NOT NULL UNIQUE,
  phone TEXT DEFAULT '',
  mobile TEXT DEFAULT '',
  has_whatsapp BOOLEAN DEFAULT FALSE,
  password_hash TEXT NOT NULL,
  status TEXT DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
  fullvendor_customer_id INTEGER,
  created_at TIMESTAMPTZ DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS admin_users (
  id SERIAL PRIMARY KEY,
  email TEXT NOT NULL UNIQUE
);

-- Seed your admin email
INSERT INTO admin_users (email) VALUES ('nudman.yonatan@gmail.com')
ON CONFLICT (email) DO NOTHING;
