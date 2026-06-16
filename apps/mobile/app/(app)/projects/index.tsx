import { useCallback, useState } from "react";
import { View, Text, FlatList, TouchableOpacity, TextInput, StyleSheet, ActivityIndicator, Modal, Alert } from "react-native";
import { useFocusEffect, useRouter } from "expo-router";
import { api } from "../../../lib/api";

type Project = { id: string; title: string; description?: string; status: string; createdAt: string };

export default function ProjectsScreen() {
  const router = useRouter();
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [createModal, setCreateModal] = useState(false);
  const [title, setTitle] = useState("");
  const [desc, setDesc] = useState("");
  const [creating, setCreating] = useState(false);

  async function load() {
    setLoading(true);
    try {
      setProjects(await api.listProjects() as Project[]);
    } catch {}
    setLoading(false);
  }

  useFocusEffect(useCallback(() => { load(); }, []));

  async function create() {
    if (!title.trim()) return;
    setCreating(true);
    try {
      await api.createProject({ title: title.trim(), description: desc || undefined });
      setCreateModal(false);
      setTitle("");
      setDesc("");
      load();
    } catch (err: unknown) {
      Alert.alert("Error", (err as { message?: string }).message ?? "Failed to create project");
    }
    setCreating(false);
  }

  const STATUS_COLORS: Record<string, string> = {
    OPEN: "#dbeafe", IN_PROGRESS: "#fef9c3", COMPLETED: "#dcfce7", CANCELLED: "#fee2e2",
  };

  return (
    <View style={s.root}>
      <View style={s.header}>
        <Text style={s.title}>Projects</Text>
        <TouchableOpacity style={s.fab} onPress={() => setCreateModal(true)}>
          <Text style={s.fabText}>+ New</Text>
        </TouchableOpacity>
      </View>
      {loading ? <ActivityIndicator color="#2563eb" style={{ marginTop: 32 }} /> : (
        <FlatList
          data={projects}
          keyExtractor={i => i.id}
          contentContainerStyle={{ padding: 16, gap: 12 }}
          ListEmptyComponent={<Text style={s.empty}>No projects yet. Tap "+ New" to create one.</Text>}
          renderItem={({ item }) => (
            <TouchableOpacity style={s.card} onPress={() => router.push({ pathname: "/(app)/projects/[id]", params: { id: item.id } })}>
              <View style={s.cardTop}>
                <Text style={s.name}>{item.title}</Text>
                <View style={[s.badge, { backgroundColor: STATUS_COLORS[item.status] ?? "#f3f4f6" }]}>
                  <Text style={s.badgeText}>{item.status}</Text>
                </View>
              </View>
              {item.description && <Text style={s.desc} numberOfLines={2}>{item.description}</Text>}
            </TouchableOpacity>
          )}
        />
      )}

      <Modal visible={createModal} animationType="slide" transparent>
        <View style={s.overlay}>
          <View style={s.modal}>
            <Text style={s.modalTitle}>New project</Text>
            <TextInput style={s.input} placeholder="Title" value={title} onChangeText={setTitle} />
            <TextInput style={[s.input, s.multiline]} placeholder="Description (optional)" value={desc} onChangeText={setDesc} multiline numberOfLines={3} />
            <TouchableOpacity style={s.btn} onPress={create} disabled={creating || !title.trim()}>
              <Text style={s.btnText}>{creating ? "Creating…" : "Create"}</Text>
            </TouchableOpacity>
            <TouchableOpacity style={s.cancelBtn} onPress={() => setCreateModal(false)}>
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
  header: { flexDirection: "row", alignItems: "center", justifyContent: "space-between", padding: 20, paddingTop: 20 },
  title: { fontSize: 22, fontWeight: "800", color: "#111827" },
  fab: { backgroundColor: "#2563eb", borderRadius: 8, paddingHorizontal: 14, paddingVertical: 8 },
  fabText: { color: "#fff", fontWeight: "700" },
  empty: { color: "#9ca3af", textAlign: "center", marginTop: 32 },
  card: { backgroundColor: "#fff", borderRadius: 10, padding: 14, borderWidth: 1, borderColor: "#e5e7eb" },
  cardTop: { flexDirection: "row", justifyContent: "space-between", alignItems: "center" },
  name: { fontSize: 16, fontWeight: "700", color: "#111827", flex: 1, marginRight: 8 },
  badge: { borderRadius: 12, paddingHorizontal: 8, paddingVertical: 3 },
  badgeText: { fontSize: 12, fontWeight: "600", color: "#374151" },
  desc: { fontSize: 13, color: "#6b7280", marginTop: 6 },
  overlay: { flex: 1, backgroundColor: "rgba(0,0,0,0.4)", justifyContent: "flex-end" },
  modal: { backgroundColor: "#fff", borderTopLeftRadius: 16, borderTopRightRadius: 16, padding: 24, gap: 12 },
  modalTitle: { fontSize: 18, fontWeight: "700", color: "#111827" },
  input: { backgroundColor: "#f9fafb", borderWidth: 1, borderColor: "#d1d5db", borderRadius: 8, padding: 12, fontSize: 15 },
  multiline: { height: 80, textAlignVertical: "top" },
  btn: { backgroundColor: "#2563eb", borderRadius: 8, paddingVertical: 14, alignItems: "center" },
  btnText: { color: "#fff", fontWeight: "700", fontSize: 15 },
  cancelBtn: { alignItems: "center", paddingVertical: 12 },
  cancelText: { color: "#6b7280" },
});
