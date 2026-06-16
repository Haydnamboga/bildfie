import { useState } from "react";
import { View, Text, TextInput, TouchableOpacity, StyleSheet, KeyboardAvoidingView, Platform, ScrollView } from "react-native";
import { useRouter } from "expo-router";
import { api } from "../../lib/api";
import { TokenStore } from "../../lib/token";

export default function SignupScreen() {
  const router = useRouter();
  const [fullName, setFullName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);

  async function submit() {
    setError("");
    if (password.length < 8) { setError("Password must be at least 8 characters"); return; }
    setLoading(true);
    try {
      const res = await api.register({ fullName, email, password });
      TokenStore.set(res.accessToken, res.refreshToken);
      router.replace("/(app)/");
    } catch (err: unknown) {
      setError((err as { message?: string }).message ?? "Registration failed");
    } finally {
      setLoading(false);
    }
  }

  return (
    <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === "ios" ? "padding" : undefined}>
      <ScrollView contentContainerStyle={s.root} keyboardShouldPersistTaps="handled">
        <Text style={s.title}>Create account</Text>
        <Text style={s.subtitle}>Join bildfie — hire or get hired</Text>

        <TextInput style={s.input} placeholder="Full name" value={fullName} onChangeText={setFullName} />
        <TextInput style={s.input} placeholder="Email" autoCapitalize="none" keyboardType="email-address" value={email} onChangeText={setEmail} />
        <TextInput style={s.input} placeholder="Password (min 8 chars)" secureTextEntry value={password} onChangeText={setPassword} />
        {!!error && <Text style={s.error}>{error}</Text>}

        <TouchableOpacity style={s.btn} onPress={submit} disabled={loading}>
          <Text style={s.btnText}>{loading ? "Creating account…" : "Sign up"}</Text>
        </TouchableOpacity>

        <TouchableOpacity onPress={() => router.push("/(auth)/")} style={s.link}>
          <Text style={s.linkText}>Already have an account? Log in</Text>
        </TouchableOpacity>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const s = StyleSheet.create({
  root: { flexGrow: 1, backgroundColor: "#f9fafb", padding: 24, justifyContent: "center", gap: 12 },
  title: { fontSize: 28, fontWeight: "800", color: "#111827", marginBottom: 4 },
  subtitle: { fontSize: 15, color: "#6b7280", marginBottom: 12 },
  input: { backgroundColor: "#fff", borderWidth: 1, borderColor: "#d1d5db", borderRadius: 8, padding: 14, fontSize: 15 },
  error: { color: "#dc2626", fontSize: 14 },
  btn: { backgroundColor: "#2563eb", borderRadius: 8, paddingVertical: 15, alignItems: "center", marginTop: 4 },
  btnText: { color: "#fff", fontWeight: "700", fontSize: 16 },
  link: { alignItems: "center", marginTop: 8 },
  linkText: { color: "#2563eb", fontSize: 14 },
});
