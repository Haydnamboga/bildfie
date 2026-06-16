import { useState } from "react";
import { View, Text, TextInput, TouchableOpacity, FlatList, StyleSheet, ActivityIndicator, Modal, Alert } from "react-native";
import { api } from "../../lib/api";

type Pro = { id: string; fullName: string; headline?: string; hourlyRate?: number; avgRating?: number };

export default function MarketplaceScreen() {
  const [q, setQ] = useState("");
  const [results, setResults] = useState<Pro[]>([]);
  const [loading, setLoading] = useState(false);
  const [offerModal, setOfferModal] = useState<Pro | null>(null);
  const [offerMsg, setOfferMsg] = useState("");
  const [offerAmt, setOfferAmt] = useState("");
  const [sending, setSending] = useState(false);

  async function search() {
    setLoading(true);
    try {
      const res = await api.searchProfessionals({ q });
      setResults((res.data ?? res) as Pro[]);
    } catch {}
    setLoading(false);
  }

  async function sendOffer() {
    if (!offerModal) return;
    setSending(true);
    try {
      await api.createOffer({ toUserId: offerModal.id, message: offerMsg, amount: offerAmt ? Number(offerAmt) : undefined });
      Alert.alert("Offer sent", `Your offer was sent to ${offerModal.fullName}.`);
      setOfferModal(null);
      setOfferMsg("");
      setOfferAmt("");
    } catch (err: unknown) {
      Alert.alert("Error", (err as { message?: string }).message ?? "Failed to send offer");
    }
    setSending(false);
  }

  return (
    <View style={s.root}>
      <View style={s.header}>
        <Text style={s.title}>Explore</Text>
      </View>
      <View style={s.searchRow}>
        <TextInput style={s.input} placeholder="Search by name or skill…" value={q} onChangeText={setQ} returnKeyType="search" onSubmitEditing={search} />
        <TouchableOpacity style={s.btn} onPress={search}>
          <Text style={s.btnText}>Go</Text>
        </TouchableOpacity>
      </View>
      {loading && <ActivityIndicator color="#2563eb" style={{ marginTop: 24 }} />}
      <FlatList
        data={results}
        keyExtractor={i => i.id}
        contentContainerStyle={{ padding: 16, gap: 12 }}
        ListEmptyComponent={!loading ? <Text style={s.empty}>Search for professionals above.</Text> : null}
        renderItem={({ item }) => (
          <View style={s.card}>
            <View style={s.cardTop}>
              <View style={{ flex: 1 }}>
                <Text style={s.name}>{item.fullName}</Text>
                {item.headline && <Text style={s.sub}>{item.headline}</Text>}
              </View>
              {item.hourlyRate != null && <Text style={s.rate}>${item.hourlyRate}/hr</Text>}
            </View>
            {item.avgRating != null && <Text style={s.rating}>★ {Number(item.avgRating).toFixed(1)}</Text>}
            <TouchableOpacity style={s.offerBtn} onPress={() => setOfferModal(item)}>
              <Text style={s.offerBtnText}>Send offer</Text>
            </TouchableOpacity>
          </View>
        )}
      />

      <Modal visible={!!offerModal} animationType="slide" transparent>
        <View style={s.overlay}>
          <View style={s.modal}>
            <Text style={s.modalTitle}>Offer to {offerModal?.fullName}</Text>
            <TextInput style={s.input} placeholder="Message (optional)" value={offerMsg} onChangeText={setOfferMsg} multiline />
            <TextInput style={s.input} placeholder="Amount (optional, USD)" keyboardType="numeric" value={offerAmt} onChangeText={setOfferAmt} />
            <TouchableOpacity style={s.btn} onPress={sendOffer} disabled={sending}>
              <Text style={s.btnText}>{sending ? "Sending…" : "Send offer"}</Text>
            </TouchableOpacity>
            <TouchableOpacity style={s.cancelBtn} onPress={() => setOfferModal(null)}>
              <Text style={s.cancelText}>Cancel</Text>
            </TouchableOpacity>
          </View>
        </View>
      </Modal>
    </View>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: "#f9fafb" },
  header: { padding: 20, paddingTop: 56 },
  title: { fontSize: 24, fontWeight: "800", color: "#111827" },
  searchRow: { flexDirection: "row", gap: 8, paddingHorizontal: 16, paddingBottom: 8 },
  input: { flex: 1, backgroundColor: "#fff", borderWidth: 1, borderColor: "#d1d5db", borderRadius: 8, padding: 12, fontSize: 15 },
  btn: { backgroundColor: "#2563eb", borderRadius: 8, paddingHorizontal: 18, alignItems: "center", justifyContent: "center" },
  btnText: { color: "#fff", fontWeight: "700" },
  empty: { color: "#9ca3af", textAlign: "center", marginTop: 32 },
  card: { backgroundColor: "#fff", borderRadius: 10, padding: 14, borderWidth: 1, borderColor: "#e5e7eb" },
  cardTop: { flexDirection: "row", alignItems: "flex-start" },
  name: { fontSize: 16, fontWeight: "700", color: "#111827" },
  sub: { fontSize: 13, color: "#6b7280", marginTop: 2 },
  rate: { fontSize: 15, fontWeight: "700", color: "#2563eb" },
  rating: { fontSize: 13, color: "#f59e0b", marginTop: 6 },
  offerBtn: { marginTop: 10, borderWidth: 1, borderColor: "#2563eb", borderRadius: 6, paddingVertical: 8, alignItems: "center" },
  offerBtnText: { color: "#2563eb", fontWeight: "600" },
  overlay: { flex: 1, backgroundColor: "rgba(0,0,0,0.4)", justifyContent: "flex-end" },
  modal: { backgroundColor: "#fff", borderTopLeftRadius: 16, borderTopRightRadius: 16, padding: 24, gap: 12 },
  modalTitle: { fontSize: 18, fontWeight: "700", color: "#111827", marginBottom: 4 },
  cancelBtn: { alignItems: "center", paddingVertical: 12 },
  cancelText: { color: "#6b7280" },
});
