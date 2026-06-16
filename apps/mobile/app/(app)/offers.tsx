import { useCallback, useState } from "react";
import { View, Text, FlatList, TouchableOpacity, StyleSheet, ActivityIndicator, Alert } from "react-native";
import { useFocusEffect } from "expo-router";
import { api } from "../../lib/api";

type Offer = { id: string; status: string; amount?: number; message?: string; fromUser?: { fullName: string }; toUser?: { fullName: string } };
type OffersData = { sent: Offer[]; received: Offer[] };

export default function OffersScreen() {
  const [data, setData] = useState<OffersData>({ sent: [], received: [] });
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState<"received" | "sent">("received");

  async function load() {
    setLoading(true);
    try {
      const res = await api.myOffers() as OffersData;
      setData(res);
    } catch {}
    setLoading(false);
  }

  useFocusEffect(useCallback(() => { load(); }, []));

  async function respond(id: string, action: "accept" | "decline") {
    try {
      if (action === "accept") await api.acceptOffer(id);
      else await api.declineOffer(id);
      load();
    } catch (err: unknown) {
      Alert.alert("Error", (err as { message?: string }).message ?? "Action failed");
    }
  }

  const items = tab === "received" ? data.received : data.sent;

  return (
    <View style={s.root}>
      <View style={s.header}>
        <Text style={s.title}>Offers</Text>
      </View>
      <View style={s.tabs}>
        <TouchableOpacity style={[s.tabBtn, tab === "received" && s.tabActive]} onPress={() => setTab("received")}>
          <Text style={[s.tabText, tab === "received" && s.tabActiveText]}>Received ({data.received.length})</Text>
        </TouchableOpacity>
        <TouchableOpacity style={[s.tabBtn, tab === "sent" && s.tabActive]} onPress={() => setTab("sent")}>
          <Text style={[s.tabText, tab === "sent" && s.tabActiveText]}>Sent ({data.sent.length})</Text>
        </TouchableOpacity>
      </View>
      {loading ? <ActivityIndicator color="#2563eb" style={{ marginTop: 32 }} /> : (
        <FlatList
          data={items}
          keyExtractor={i => i.id}
          contentContainerStyle={{ padding: 16, gap: 12 }}
          ListEmptyComponent={<Text style={s.empty}>No {tab} offers.</Text>}
          renderItem={({ item }) => (
            <View style={s.card}>
              <View style={s.cardTop}>
                <Text style={s.name}>
                  {tab === "received" ? item.fromUser?.fullName : item.toUser?.fullName}
                </Text>
                <View style={[s.badge, item.status === "ACCEPTED" && s.green, item.status === "DECLINED" && s.red]}>
                  <Text style={s.badgeText}>{item.status}</Text>
                </View>
              </View>
              {item.message && <Text style={s.msg}>{item.message}</Text>}
              {item.amount != null && <Text style={s.amount}>${item.amount}</Text>}
              {tab === "received" && item.status === "PENDING" && (
                <View style={s.actions}>
                  <TouchableOpacity style={s.acceptBtn} onPress={() => respond(item.id, "accept")}>
                    <Text style={s.acceptText}>Accept</Text>
                  </TouchableOpacity>
                  <TouchableOpacity style={s.declineBtn} onPress={() => respond(item.id, "decline")}>
                    <Text style={s.declineText}>Decline</Text>
                  </TouchableOpacity>
                </View>
              )}
            </View>
          )}
        />
      )}
    </View>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: "#f9fafb" },
  header: { padding: 20, paddingTop: 56 },
  title: { fontSize: 24, fontWeight: "800", color: "#111827" },
  tabs: { flexDirection: "row", gap: 8, paddingHorizontal: 16, paddingBottom: 8 },
  tabBtn: { flex: 1, borderRadius: 8, paddingVertical: 10, alignItems: "center", backgroundColor: "#f3f4f6", borderWidth: 1, borderColor: "#e5e7eb" },
  tabActive: { backgroundColor: "#2563eb", borderColor: "#2563eb" },
  tabText: { fontWeight: "600", color: "#6b7280" },
  tabActiveText: { color: "#fff" },
  empty: { color: "#9ca3af", textAlign: "center", marginTop: 32 },
  card: { backgroundColor: "#fff", borderRadius: 10, padding: 14, borderWidth: 1, borderColor: "#e5e7eb" },
  cardTop: { flexDirection: "row", justifyContent: "space-between", alignItems: "center" },
  name: { fontSize: 16, fontWeight: "700", color: "#111827" },
  badge: { backgroundColor: "#f3f4f6", borderRadius: 12, paddingHorizontal: 8, paddingVertical: 3 },
  green: { backgroundColor: "#dcfce7" },
  red: { backgroundColor: "#fee2e2" },
  badgeText: { fontSize: 12, fontWeight: "600", color: "#374151" },
  msg: { fontSize: 14, color: "#6b7280", marginTop: 6 },
  amount: { fontSize: 15, fontWeight: "700", color: "#2563eb", marginTop: 4 },
  actions: { flexDirection: "row", gap: 8, marginTop: 10 },
  acceptBtn: { flex: 1, backgroundColor: "#2563eb", borderRadius: 6, paddingVertical: 9, alignItems: "center" },
  acceptText: { color: "#fff", fontWeight: "700" },
  declineBtn: { flex: 1, borderWidth: 1, borderColor: "#d1d5db", borderRadius: 6, paddingVertical: 9, alignItems: "center" },
  declineText: { color: "#374151", fontWeight: "600" },
});
