import { Tabs, useRouter } from "expo-router";
import { useEffect } from "react";
import { TokenStore } from "../../lib/token";

export default function AppLayout() {
  const router = useRouter();

  useEffect(() => {
    if (!TokenStore.get()) {
      router.replace("/(auth)/");
    }
  }, []);

  return (
    <Tabs
      screenOptions={{
        tabBarActiveTintColor: "#2563eb",
        tabBarInactiveTintColor: "#9ca3af",
        tabBarStyle: { borderTopColor: "#e5e7eb" },
        headerShown: false,
      }}
    >
      <Tabs.Screen name="index" options={{ title: "Home" }} />
      <Tabs.Screen name="marketplace" options={{ title: "Explore" }} />
      <Tabs.Screen name="projects" options={{ title: "Projects" }} />
      <Tabs.Screen name="offers" options={{ title: "Offers" }} />
      <Tabs.Screen name="profile" options={{ title: "Profile" }} />
    </Tabs>
  );
}
