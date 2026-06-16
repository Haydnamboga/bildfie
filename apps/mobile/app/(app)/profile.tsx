import { useCallback, useState } from "react";
import { View, Text, TextInput, TouchableOpacity, ScrollView, StyleSheet, ActivityIndicator, Alert } from "react-native";
import { useFocusEffect, useRouter } from "expo-router";
import { api } from "../../lib/api";
import { TokenStore } from "../../lib/token";

type Profile = { id: string; fullName: string; email: string; headline?: string; bio?: string; skills?: string[]; hourlyRate?: number };

export default function ProfileScreen() {
  const router = useRouter();
  const [profile, setProfile] = useState<Profile | null>(null);
  const [form, setForm] = useState({ fullName: "", headline: "", bio: "", skills: "", hourlyRate: "" });
  const [saving, setSaving] = useState(false);
  const [loading, setLoading] = useState(true);

  useFocusEffect(useCallback(() => {
    api.me().then(u => {
      const p = u as unknown as Profile;
      setProfile(p);
      setForm({
        fullName: p.fullName ?? "",
        headline: p.headline ?? "",
        bio: p.bio ?? "",
        skills: (p.skills ?? []).join(", "),
        hourlyRate: p.hourlyRate != null ? String(p.hourlyRate) : "",
      });
    }).catch(() => {}).finally(() => setLoading(false));
  }, []));

  async function save() {
    setSaving(true);
    try {
      await api.updateProfile({
        fullName: form.fullName || undefined,
        headline: form.headline || undefined,
        bio: form.bio || undefined,
        skills: form.skills ? form.skills.split(",").map(s => s.trim()).filter(Boolean) : undefined,
        hourlyRate: form.hourlyRate ? Number(form.hourlyRate) : undefined,
      });
      Alert.alert("Saved", "Profile updated.");
    } catch (err: unknown) {
      Alert.alert("Error", (err as { message?: string }).message ?? "Save failed");
    }
    setSaving(false);
  }

  function logout() {
    const rt = TokenStore.getRefresh();
    if (rt) api.logout(rt).catch(() => {});
    TokenStore.clear();
    router.replace("/(auth)/");
  }

  if (loading) return <View style={s.center}><ActivityIndicator color="#2563eb" /></View>;

  return (
    <ScrollView style={s.root} contentContainerStyle={{ padding: 20, paddingTop: 56, gap: 14 }}>
      <Text style={s.title}>Profile</Text>
      {profile && <Text style={s.email}>{profile.email}</Text>}

      <Text style={s.label}>Full name</Text>
      <TextInput style={s.input} value={form.fullName} onChangeText={v => setForm(f => ({ ...f, fullName: v }))} />

      <Text style={s.label}>Headline</Text>
      <TextInput style={s.input} value={form.headline} onChangeText={v => setForm(f => ({ ...f, headline: v }))} placeholder="e.g. Senior electrician" />

      <Text style={s.label}>Bio</Text>
      <TextInput style={[s.input, s.multiline]} value={form.bio} onChangeText={v => setForm(f => ({ ...f, bio: v }))} multiline numberOfLines={4} />

      <Text style={s.label}>Skills (comma-separated)</Text>
      <TextInput style={s.input} value={form.skills} onChangeText={v => setForm(f => ({ ...f, skills: v }))} placeholder="e.g. Plumbing, Tiling" />

      <Text style={s.label}>Hourly rate (USD)</Text>
      <TextInput style={s.input} value={form.hourlyRate} onChangeText={v => setForm(f => ({ ...f, hourlyRate: v }))} keyboardType="numeric" placeholder="e.g. 50" />

      <TouchableOpacity style={s.btn} onPress={save} disabled={saving}>
        <Text style={s.btnText}>{saving ? "Saving…" : "Save changes"}</Text>
      </TouchableOpacity>

      <TouchableOpacity style={s.logoutBtn} onPress={logout}>
        <Text style={s.logoutText}>Log out</Text>
      </TouchableOpacity>
    </ScrollView>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: "#f9fafb" },
  center: { flex: 1, alignItems: "center", justifyContent: "center" },
  title: { fontSize: 24, fontWeight: "800", color: "#111827" },
  email: { fontSize: 14, color: "#6b7280", marginTop: -8 },
  label: { fontSize: 13, fontWeight: "600", color: "#374151" },
  input: { backgroundColor: "#fff", borderWidth: 1, borderColor: "#d1d5db", borderRadius: 8, padding: 12, fontSize: 15 },
  multiline: { height: 100, textAlignVertical: "top" },
  btn: { backgroundColor: "#2563eb", borderRadius: 8, paddingVertical: 15, alignItems: "center" },
  btnText: { color: "#fff", fontWeight: "700", fontSize: 16 },
  logoutBtn: { borderWidth: 1, borderColor: "#fca5a5", borderRadius: 8, paddingVertical: 15, alignItems: "center" },
  logoutText: { color: "#dc2626", fontWeight: "600" },
});
