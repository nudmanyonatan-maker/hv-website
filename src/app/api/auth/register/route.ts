import { NextRequest, NextResponse } from 'next/server';
import { registerSchema } from '@/lib/validation';
import { sendBirdviewEmail } from '@/lib/birdview';
import { env } from '@/lib/env';

function escapeHtml(str: string): string {
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();
    const parsed = registerSchema.safeParse(body);
    if (!parsed.success) {
      return NextResponse.json({ error: parsed.error.issues[0].message }, { status: 400 });
    }
    const {
      contactName, companyName, taxId, address,
      email, phone, mobile, hasWhatsapp, password,
    } = parsed.data;

    // Send registration info to admin via Birdview email
    const htmlBody = `
      <div style="font-family:sans-serif;max-width:600px;margin:0 auto;">
        <h2 style="color:#b91c1c;">New Customer Registration</h2>
        <p>A new wholesale customer has registered on the Home Value website and needs to be set up in FullVendor.</p>
        <table style="width:100%;border-collapse:collapse;margin:20px 0;">
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;width:40%;">Contact Name</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(contactName)}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Company Name</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(companyName)}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Tax ID</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(taxId || 'Not provided')}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Address</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(address || 'Not provided')}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Email</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(email)}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Phone</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(phone || 'Not provided')}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Mobile</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(mobile || 'Not provided')}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">WhatsApp</td><td style="padding:8px;border-bottom:1px solid #eee;">${hasWhatsapp ? 'Yes' : 'No'}</td></tr>
          <tr><td style="padding:8px;border-bottom:1px solid #eee;font-weight:bold;">Requested Password</td><td style="padding:8px;border-bottom:1px solid #eee;">${escapeHtml(password)}</td></tr>
        </table>
        <p style="color:#666;font-size:14px;">Please create this customer in FullVendor and set up their login credentials. Then notify them at <strong>${escapeHtml(email)}</strong> that their account is ready.</p>
      </div>
    `;

    const result = await sendBirdviewEmail(
      [env.ADMIN_EMAIL],
      `New Registration: ${companyName} — ${contactName}`,
      htmlBody,
      true
    );

    if (!result.success) {
      console.error('[register] Birdview email failed');
      return NextResponse.json({ error: 'Failed to submit registration. Please try again.' }, { status: 500 });
    }

    return NextResponse.json({ success: true, message: 'Registration submitted' });
  } catch (error) {
    console.error('Registration error:', error);
    return NextResponse.json({ error: 'Registration failed. Please try again.' }, { status: 500 });
  }
}
