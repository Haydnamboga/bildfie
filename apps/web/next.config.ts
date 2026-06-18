import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  output: "standalone",
  outputFileTracingRoot: require("path").join(__dirname, "../../"),
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
