import { Resend } from 'resend';
import { env } from './env';
import { generateApprovalToken } from './approval-token';
import { sendBirdviewEmail } from './birdview';

function getResend(): Resend | null {
  if (!env.RESEND_API_KEY) {
    console.warn('[email] RESEND_API_KEY not set — emails disabled');
    return null;
  }
  return new Resend(env.RESEND_API_KEY);
}

function escapeHtml(str: string): string {
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

const FROM = 'Home Value <noreply@hv-website-phi.vercel.app>';
const ADMIN_EMAIL = env.ADMIN_EMAIL;

export async function sendRegistrationNotification(data: {
  contactName: string;
  companyName: string;
  taxId: string;
  email: string;
  phone: string;
  mobile: string;
  address: string;
  hasWhatsapp: boolean;
  registrationId: number;
}) {
  const token = generateApprovalToken(data.registrationId, env.JWT_SECRET);
  const approveUrl = `${env.SITE_URL}/api/admin/registrations/approve?token=${token}&action=approve`;
  const rejectUrl = `${env.SITE_URL}/api/admin/registrations/approve?token=${token}&action=reject`;

  const subject = `New Registration: ${data.companyName} — ${data.contactName}`;
  const html = `
        <h2>New Customer Registration</h2>
        <table style="border-collapse:collapse;width:100%;max-width:500px;">
          <tr><td style="padding:8px;font-weight:bold;">Contact Name</td><td style="padding:8px;">${escapeHtml(data.contactName)}</td></tr>
          <tr><td style="padding:8px;font-weight:bold;">Company</td><td style="padding:8px;">${escapeHtml(data.companyName)}</td></tr>
          <tr><td style="padding:8px;font-weight:bold;">Tax ID</td><td style="padding:8px;">${escapeHtml(data.taxId)}</td></tr>
          <tr><td style="padding:8px;font-weight:bold;">Email</td><td style="padding:8px;">${escapeHtml(data.email)}</td></tr>
          <tr><td style="padding:8px;font-weight:bold;">Phone</td><td style="padding:8px;">${escapeHtml(data.phone)}</td></tr>
          <tr><td style="padding:8px;font-weight:bold;">Mobile</td><td style="padding:8px;">${escapeHtml(data.mobile)}</td></tr>
          <tr><td style="padding:8px;font-weight:bold;">Address</td><td style="padding:8px;">${escapeHtml(data.address)}</td></tr>
          <tr><td style="padding:8px;font-weight:bold;">WhatsApp</td><td style="padding:8px;">${data.hasWhatsapp ? 'Yes' : 'No'}</td></tr>
        </table>
        <br/>
        <a href="${approveUrl}" style="display:inline-block;padding:12px 24px;background:#22c55e;color:white;text-decoration:none;border-radius:6px;margin-right:12px;">Approve</a>
        <a href="${rejectUrl}" style="display:inline-block;padding:12px 24px;background:#ef4444;color:white;text-decoration:none;border-radius:6px;">Reject</a>
      `;

  // Try Resend first, fall back to Birdview
  const resend = getResend();
  if (resend) {
    try {
      await resend.emails.send({ from: FROM, to: ADMIN_EMAIL, subject, html });
      return { success: true };
    } catch (error) {
      console.warn('[email] Resend failed, trying Birdview:', error);
    }
  }

  // Fallback: Birdview email API (no auth required)
  try {
    const result = await sendBirdviewEmail([ADMIN_EMAIL], subject, html, true);
    return result;
  } catch (bvError) {
    console.error('[email] Both Resend and Birdview failed:', bvError);
    return { success: false };
  }
}

export async function sendApprovalEmail(email: string, name: string) {
  const subject = 'Your Home Value Account Has Been Approved!';
  const html = `
        <h2>Welcome, ${escapeHtml(name)}!</h2>
        <p>Your Home Value wholesale account has been approved. You can now log in to browse our full catalog with prices and place orders.</p>
        <a href="${env.SITE_URL}/en/login" style="display:inline-block;padding:12px 24px;background:#2563eb;color:white;text-decoration:none;border-radius:6px;">Log In Now</a>
      `;

  // Try Resend first, fall back to Birdview
  const resend = getResend();
  if (resend) {
    try {
      await resend.emails.send({ from: FROM, to: email, subject, html });
      return { success: true };
    } catch (error) {
      console.warn('[email] Resend failed, trying Birdview:', error);
    }
  }

  // Fallback: Birdview email API (no auth required)
  try {
    const result = await sendBirdviewEmail([email], subject, html, true);
    return result;
  } catch (bvError) {
    console.error('[email] Both Resend and Birdview failed:', bvError);
    return { success: false };
  }
}

export async function sendRejectionEmail(email: string, name: string) {
  const subject = 'Home Value Registration Update';
  const html = `
        <p>Dear ${escapeHtml(name)},</p>
        <p>Thank you for your interest in Home Value. Unfortunately, we are unable to approve your account at this time. Please contact us if you have any questions.</p>
      `;

  // Try Resend first, fall back to Birdview
  const resend = getResend();
  if (resend) {
    try {
      await resend.emails.send({ from: FROM, to: email, subject, html });
      return { success: true };
    } catch (error) {
      console.warn('[email] Resend failed, trying Birdview:', error);
    }
  }

  // Fallback: Birdview email API (no auth required)
  try {
    const result = await sendBirdviewEmail([email], subject, html, true);
    return result;
  } catch (bvError) {
    console.error('[email] Both Resend and Birdview failed:', bvError);
    return { success: false };
  }
}

export async function sendContactForm(data: {
  name: string;
  email: string;
  subject: string;
  message: string;
}) {
  const subject = `Contact Form: ${data.subject}`;
  const html = `
        <h2>Contact Form Submission</h2>
        <p><strong>From:</strong> ${escapeHtml(data.name)} (${escapeHtml(data.email)})</p>
        <p><strong>Subject:</strong> ${escapeHtml(data.subject)}</p>
        <p>${escapeHtml(data.message)}</p>
      `;

  // Try Resend first, fall back to Birdview
  const resend = getResend();
  if (resend) {
    try {
      await resend.emails.send({ from: FROM, to: ADMIN_EMAIL, replyTo: data.email, subject, html });
      return { success: true };
    } catch (error) {
      console.warn('[email] Resend failed, trying Birdview:', error);
    }
  }

  // Fallback: Birdview email API (no auth required)
  try {
    const result = await sendBirdviewEmail([ADMIN_EMAIL], subject, html, true);
    return result;
  } catch (bvError) {
    console.error('[email] Both Resend and Birdview failed:', bvError);
    return { success: false };
  }
}
