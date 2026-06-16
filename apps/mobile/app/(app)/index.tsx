import { useEffect, useState } from "react";
import { View, Text, ScrollView, TouchableOpacity, StyleSheet, ActivityIndicator, RefreshControl } from "react-native";
import { useRouter } from "expo-router";
import { api } from "../../lib/api";
import { TokenStore } from "../../lib/token";

const API_BASE = process.env.EXPO_PUBLIC_API_URL ?? "http://localhost:4000";

type Project = { id: string; title: string; status: string };
type Notification = { id: string; message: string; read: boolean; createdAt: string };

export default function Dashboard() {
  const router = useRouter();
  const [projects, setProjects] = useState<Project[]>([]);
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  async function load() {
    try {
      const proj = await api.listProjects() as Project[];
      setProjects(proj.slice(0, 5));
      const token = TokenStore.get();
      const notifRes = await fetch(`${API_BASE}/notifications`, {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
      }).then(r => r.ok ? r.json() : []).catch(() => []);
      setNotifications((notifRes as Notification[]).slice(0, 5));
    } catch {}
    setLoading(false);
    setRefreshing(false);
  }

  useEffect(() => { load(); }, []);

  const unread = notifications.filter(n => !n.read).length;

  if (loading) return <View style={s.center}><ActivityIndicator color="#2563eb" /></View>;

  return (
    <ScrollView style={s.root} refreshControl={<RefreshControl refreshing={refreshing} onRefresh={() => { setRefreshing(true); load(); }} />}>
      <View style={s.header}>
        <Text style={s.greeting}>bildfie</Text>
        {unread > 0 && <View style={s.badge}><Text style={s.badgeText}>{unread} new</Text></View>}
      </View>

      <View style={s.statsRow}>
        <View style={s.stat}><Text style={s.statNum}>{projects.length}</Text><Text style={s.statLabel}>Projects</Text></View>
        <View style={s.stat}><Text style={s.statNum}>{unread}</Text><Text style={s.statLabel}>Unread</Text></View>
      </View>

      <Text style={s.sectionTitle}>Recent projects</Text>
      {projects.length === 0 && <Text style={s.empty}>No projects yet. Create one in Projects tab.</Text>}
      {projects.map(p => (
        <TouchableOpacity key={p.id} style={s.card} onPress={() => router.push(`/(app)/projects/${p.id}`)}>
          <Text style={s.cardTitle}>{p.title}</Text>
          <Text style={s.cardSub}>{p.status}</Text>
        </TouchableOpacity>
      ))}

      <Text style={s.sectionTitle}>Notifications</Text>
      {notifications.length === 0 && <Text style={s.empty}>No notifications.</Text>}
      {notifications.map(n => (
        <View key={n.id} style={[s.card, !n.read && s.cardUnread]}>
          <Text style={s.cardTitle}>{n.message}</Text>
        </View>
      ))}
    </ScrollView>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: "#f9fafb" },
  center: { flex: 1, alignItems: "center", justifyContent: "center" },
  header: { flexDirection: "row", alignItems: "center", justifyContent: "space-between", padding: 20, paddingTop: 56 },
  greeting: { fontSize: 24, fontWeight: "800", color: "#2563eb" },
  badge: { backgroundColor: "#2563eb", borderRadius: 12, paddingHorizontal: 10, paddingVertical: 4 },
  badgeText: { color: "#fff", fontSize: 12, fontWeight: "700" },
  statsRow: { flexDirection: "row", gap: 12, padding: 20, paddingTop: 0 },
  stat: { flex: 1, backgroundColor: "#fff", borderRadius: 12, padding: 16, alignItems: "center", borderWidth: 1, borderColor: "#e5e7eb" },
  statNum: { fontSize: 28, fontWeight: "800", color: "#2563eb" },
  statLabel: { fontSize: 13, color: "#6b7280", marginTop: 2 },
  sectionTitle: { fontSize: 16, fontWeight: "700", color: "#111827", paddingHorizontal: 20, paddingTop: 8, paddingBottom: 8 },
  card: { backgroundColor: "#fff", marginHorizontal: 20, marginBottom: 10, borderRadius: 10, padding: 14, borderWidth: 1, borderColor: "#e5e7eb" },
  cardUnread: { borderColor: "#bfdbfe", backgroundColor: "#eff6ff" },
  cardTitle: { fontSize: 15, fontWeight: "600", color: "#111827" },
  cardSub: { fontSize: 13, color: "#6b7280", marginTop: 2 },
  empty: { color: "#9ca3af", paddingHorizontal: 20, paddingBottom: 12 },
});
