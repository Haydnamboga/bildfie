import type { ReactNode } from "react";
import { PublicNav } from "@/components/PublicNav";
import { PublicFooter } from "@/components/PublicFooter";

export default function PublicLayout({ children }: { children: ReactNode }) {
  return (
    <>
      <PublicNav />
      <main>{children}</main>
      <PublicFooter />
    </>
  );
}
