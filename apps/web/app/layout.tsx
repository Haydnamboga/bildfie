import type { Metadata } from "next";
import type { ReactNode } from "react";
import { Sora, Playfair_Display } from "next/font/google";
import "./globals.css";

const sora = Sora({
  subsets: ["latin"],
  weight: ["300", "400", "500", "600", "700", "800"],
  variable: "--font-sora",
  display: "swap",
});

const playfair = Playfair_Display({
  subsets: ["latin"],
  weight: ["700", "800"],
  style: ["normal", "italic"],
  variable: "--font-playfair",
  display: "swap",
});

export const metadata: Metadata = {
  title: {
    default: "bildfie — Build Smarter. Connect Better.",
    template: "%s | bildfie",
  },
  description:
    "Africa's trusted construction marketplace and project CRM. Find verified contractors, manage projects, and pay securely.",
  keywords: [
    "construction marketplace",
    "hire contractors Kenya",
    "project management",
    "construction CRM",
    "East Africa",
  ],
  openGraph: {
    siteName: "bildfie",
    type: "website",
  },
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html
      lang="en"
      className={`${sora.variable} ${playfair.variable}`}
    >
      <body className={sora.className}>{children}</body>
    </html>
  );
}
