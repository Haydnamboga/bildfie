import { View, Text, StyleSheet, TouchableOpacity } from "react-native";
import { useRouter } from "expo-router";

export default function WelcomeScreen() {
  const router = useRouter();

  return (
    <View style={s.root}>
      <View style={s.hero}>
        <Text style={s.logo}>bildfie</Text>
        <Text style={s.tagline}>Hire skilled tradespeople.{"\n"}Get hired for what you do best.</Text>
      </View>
      <View style={s.actions}>
        <TouchableOpacity style={s.btnPrimary} onPress={() => router.push("/(auth)/signup")}>
          <Text style={s.btnPrimaryText}>Get started</Text>
        </TouchableOpacity>
        <TouchableOpacity style={s.btnSecondary} onPress={() => router.push("/(auth)/")}>
          <Text style={s.btnSecondaryText}>Log in</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: "#fff", justifyContent: "space-between", padding: 32 },
  hero: { flex: 1, justifyContent: "center", alignItems: "center", gap: 16 },
  logo: { fontSize: 40, fontWeight: "800", color: "#2563eb" },
  tagline: { fontSize: 18, color: "#374151", textAlign: "center", lineHeight: 28 },
  actions: { gap: 12, paddingBottom: 16 },
  btnPrimary: { backgroundColor: "#2563eb", borderRadius: 10, paddingVertical: 16, alignItems: "center" },
  btnPrimaryText: { color: "#fff", fontWeight: "700", fontSize: 16 },
  btnSecondary: { borderWidth: 1, borderColor: "#d1d5db", borderRadius: 10, paddingVertical: 16, alignItems: "center" },
  btnSecondaryText: { color: "#374151", fontWeight: "600", fontSize: 16 },
});
