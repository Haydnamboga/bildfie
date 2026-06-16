import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  transpilePackages: [
    "@bildfie/auth",
    "@bildfie/api-client",
    "@bildfie/types",
    "@bildfie/validation",
    "@bildfie/ui",
    "@bildfie/config",
  ],
};

export default nextConfig;
