import { useCallback, useRef, useState } from "react";
import { View, Text, TextInput, TouchableOpacity, ScrollView, FlatList, StyleSheet, ActivityIndicator, Alert, KeyboardAvoidingView, Platform } from "react-native";
import { useFocusEffect, useLocalSearchParams, useNavigation } from "expo-router";
import { api } from "../../../lib/api";

type Task = { id: string; title: string; status: string };
type Milestone = { id: string; title: string; amount: number; status: string };
type Member = { id: string; user?: { fullName: string; email: string }; roleLabel?: string };
type Message = { id: string; body: string; sender?: { fullName: string }; createdAt: string };

type Tab = "tasks" | "milestones" | "team" | "messages";

export default function ProjectDetail() {
  const { id } = useLocalSearchParams<{ id: string }>();
  const navigation = useNavigation();

  const [tab, setTab] = useState<Tab>("tasks");
  const [project, setProject] = useState<{ title: string; status: string } | null>(null);

  const [tasks, setTasks] = useState<Task[]>([]);
  const [milestones, setMilestones] = useState<Milestone[]>([]);
  const [team, setTeam] = useState<Member[]>([]);
  const [messages, setMessages] = useState<Message[]>([]);
  const [loading, setLoading] = useState(true);

  const [newTaskTitle, setNewTaskTitle] = useState("");
  const [newMilestoneTitle, setNewMilestoneTitle] = useState("");
  const [newMilestoneAmt, setNewMilestoneAmt] = useState("");
  const [inviteEmail, setInviteEmail] = useState("");
  const [msgBody, setMsgBody] = useState("");
  const scrollRef = useRef<ScrollView>(null);

  async function load() {
    if (!id) return;
    setLoading(true);
    try {
      const [proj, t, m, tm, msg] = await Promise.all([
        api.getProject(id) as Promise<{ title: string; status: string }>,
        api.listTasks(id) as Promise<Task[]>,
        api.listMilestones(id) as Promise<Milestone[]>,
        api.listTeam(id) as Promise<Member[]>,
        api.listMessages(id) as Promise<Message[]>,
      ]);
      setProject(proj);
      navigation.setOptions({ title: proj.title });
      setTasks(t);
      setMilestones(m);
      setTeam(tm);
      setMessages(msg);
    } catch {}
    setLoading(false);
  }

  useFocusEffect(useCallback(() => { load(); }, [id]));

  async function addTask() {
    if (!newTaskTitle.trim() || !id) return;
    try {
      await api.createTask(id, { title: newTaskTitle.trim() });
      setNewTaskTitle("");
      setTasks(await api.listTasks(id) as Task[]);
    } catch (err: unknown) { Alert.alert("Error", (err as { message?: string }).message ?? "Failed"); }
  }

  async function toggleTask(task: Task) {
    if (!id) return;
    const next = task.status === "DONE" ? "TODO" : "DONE";
    try {
      await api.updateTask(id, task.id, { status: next });
      setTasks(t => t.map(x => x.id === task.id ? { ...x, status: next } : x));
    } catch {}
  }

  async function addMilestone() {
    if (!newMilestoneTitle.trim() || !id) return;
    try {
      await api.createMilestone(id, { title: newMilestoneTitle.trim(), amount: Number(newMilestoneAmt) || 0 });
      setNewMilestoneTitle("");
      setNewMilestoneAmt("");
      setMilestones(await api.listMilestones(id) as Milestone[]);
    } catch (err: unknown) { Alert.alert("Error", (err as { message?: string }).message ?? "Failed"); }
  }

  async function inviteMember() {
    if (!inviteEmail.trim() || !id) return;
    // Search user by email first
    try {
      const users = await api.searchUsers(inviteEmail.trim());
      if (!users.length) { Alert.alert("Not found", "No user found with that email."); return; }
      await api.inviteToTeam(id, { userId: users[0].id });
      setInviteEmail("");
      setTeam(await api.listTeam(id) as Member[]);
    } catch (err: unknown) { Alert.alert("Error", (err as { message?: string }).message ?? "Failed"); }
  }

  async function sendMessage() {
    if (!msgBody.trim() || !id) return;
    const body = msgBody.trim();
    setMsgBody("");
    try {
      await api.sendMessage(id, body);
      setMessages(await api.listMessages(id) as Message[]);
      setTimeout(() => scrollRef.current?.scrollToEnd({ animated: true }), 100);
    } catch (err: unknown) { Alert.alert("Error", (err as { message?: string }).message ?? "Failed"); }
  }

  if (loading) return <View style={s.center}><ActivityIndicator color="#2563eb" /></View>;

  const TABS: Tab[] = ["tasks", "milestones", "team", "messages"];

  return (
    <KeyboardAvoidingView style={{ flex: 1 }} behavior={Platform.OS === "ios" ? "padding" : undefined} keyboardVerticalOffset={90}>
      <View style={s.root}>
        {project && (
          <View style={s.projectHeader}>
            <Text style={s.status}>{project.status}</Text>
          </View>
        )}
        <View style={s.tabBar}>
          {TABS.map(t => (
            <TouchableOpacity key={t} style={[s.tabBtn, tab === t && s.tabActive]} onPress={() => setTab(t)}>
              <Text style={[s.tabText, tab === t && s.tabActiveText]}>{t[0].toUpperCase() + t.slice(1)}</Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* TASKS */}
        {tab === "tasks" && (
          <View style={{ flex: 1 }}>
            <View style={s.addRow}>
              <TextInput style={s.input} placeholder="New task title…" value={newTaskTitle} onChangeText={setNewTaskTitle} />
              <TouchableOpacity style={s.addBtn} onPress={addTask}>
                <Text style={s.addBtnText}>Add</Text>
              </TouchableOpacity>
            </View>
            <FlatList
              data={tasks}
              keyExtractor={i => i.id}
              contentContainerStyle={{ padding: 16, gap: 10 }}
              ListEmptyComponent={<Text style={s.empty}>No tasks yet.</Text>}
              renderItem={({ item }) => (
                <TouchableOpacity style={s.taskItem} onPress={() => toggleTask(item)}>
                  <View style={[s.check, item.status === "DONE" && s.checked]} />
                  <View style={{ flex: 1 }}>
                    <Text style={[s.taskTitle, item.status === "DONE" && s.done]}>{item.title}</Text>
                    <Text style={s.taskStatus}>{item.status}</Text>
                  </View>
                </TouchableOpacity>
              )}
            />
          </View>
        )}

        {/* MILESTONES */}
        {tab === "milestones" && (
          <View style={{ flex: 1 }}>
            <View style={s.addRow}>
              <TextInput style={[s.input, { flex: 2 }]} placeholder="Milestone title…" value={newMilestoneTitle} onChangeText={setNewMilestoneTitle} />
              <TextInput style={[s.input, { flex: 1 }]} placeholder="$amt" keyboardType="numeric" value={newMilestoneAmt} onChangeText={setNewMilestoneAmt} />
              <TouchableOpacity style={s.addBtn} onPress={addMilestone}>
                <Text style={s.addBtnText}>Add</Text>
              </TouchableOpacity>
            </View>
            <FlatList
              data={milestones}
              keyExtractor={i => i.id}
              contentContainerStyle={{ padding: 16, gap: 10 }}
              ListEmptyComponent={<Text style={s.empty}>No milestones yet.</Text>}
              renderItem={({ item }) => (
                <View style={s.card}>
                  <View style={s.cardRow}>
                    <Text style={s.cardTitle}>{item.title}</Text>
                    <Text style={s.amount}>${item.amount}</Text>
                  </View>
                  <Text style={s.taskStatus}>{item.status}</Text>
                </View>
              )}
            />
          </View>
        )}

        {/* TEAM */}
        {tab === "team" && (
          <View style={{ flex: 1 }}>
            <View style={s.addRow}>
              <TextInput style={s.input} placeholder="Invite by email…" autoCapitalize="none" value={inviteEmail} onChangeText={setInviteEmail} />
              <TouchableOpacity style={s.addBtn} onPress={inviteMember}>
                <Text style={s.addBtnText}>Invite</Text>
              </TouchableOpacity>
            </View>
            <FlatList
              data={team}
              keyExtractor={i => i.id}
              contentContainerStyle={{ padding: 16, gap: 10 }}
              ListEmptyComponent={<Text style={s.empty}>No team members yet.</Text>}
              renderItem={({ item }) => (
                <View style={s.card}>
                  <Text style={s.cardTitle}>{item.user?.fullName ?? "—"}</Text>
                  <Text style={s.taskStatus}>{item.user?.email} {item.roleLabel ? `· ${item.roleLabel}` : ""}</Text>
                </View>
              )}
            />
          </View>
        )}

        {/* MESSAGES */}
        {tab === "messages" && (
          <View style={{ flex: 1 }}>
            <ScrollView ref={scrollRef} style={{ flex: 1 }} contentContainerStyle={{ padding: 16, gap: 10 }}>
              {messages.length === 0 && <Text style={s.empty}>No messages yet.</Text>}
              {messages.map(m => (
                <View key={m.id} style={s.bubble}>
                  <Text style={s.bubbleSender}>{m.sender?.fullName ?? "Unknown"}</Text>
                  <Text style={s.bubbleBody}>{m.body}</Text>
                </View>
              ))}
            </ScrollView>
            <View style={s.msgRow}>
              <TextInput style={[s.input, { flex: 1 }]} placeholder="Message…" value={msgBody} onChangeText={setMsgBody} />
              <TouchableOpacity style={s.addBtn} onPress={sendMessage}>
                <Text style={s.addBtnText}>Send</Text>
              </TouchableOpacity>
            </View>
          </View>
        )}
      </View>
    </KeyboardAvoidingView>
  );
}

const s = StyleSheet.create({
  root: { flex: 1, backgroundColor: "#f9fafb" },
  center: { flex: 1, alignItems: "center", justifyContent: "center" },
  projectHeader: { paddingHorizontal: 16, paddingVertical: 10, backgroundColor: "#fff", borderBottomWidth: 1, borderBottomColor: "#e5e7eb" },
  status: { fontSize: 13, color: "#6b7280", fontWeight: "600" },
  tabBar: { flexDirection: "row", backgroundColor: "#fff", borderBottomWidth: 1, borderBottomColor: "#e5e7eb" },
  tabBtn: { flex: 1, paddingVertical: 12, alignItems: "center" },
  tabActive: { borderBottomWidth: 2, borderBottomColor: "#2563eb" },
  tabText: { fontSize: 13, fontWeight: "600", color: "#9ca3af" },
  tabActiveText: { color: "#2563eb" },
  addRow: { flexDirection: "row", gap: 8, padding: 12, paddingBottom: 4 },
  input: { backgroundColor: "#fff", borderWidth: 1, borderColor: "#d1d5db", borderRadius: 8, padding: 10, fontSize: 14 },
  addBtn: { backgroundColor: "#2563eb", borderRadius: 8, paddingHorizontal: 14, alignItems: "center", justifyContent: "center" },
  addBtnText: { color: "#fff", fontWeight: "700", fontSize: 14 },
  empty: { color: "#9ca3af", textAlign: "center", marginTop: 24 },
  taskItem: { flexDirection: "row", alignItems: "center", gap: 12, backgroundColor: "#fff", borderRadius: 8, padding: 12, borderWidth: 1, borderColor: "#e5e7eb" },
  check: { width: 22, height: 22, borderRadius: 11, borderWidth: 2, borderColor: "#d1d5db" },
  checked: { backgroundColor: "#2563eb", borderColor: "#2563eb" },
  taskTitle: { fontSize: 15, fontWeight: "600", color: "#111827" },
  done: { textDecorationLine: "line-through", color: "#9ca3af" },
  taskStatus: { fontSize: 12, color: "#9ca3af", marginTop: 2 },
  card: { backgroundColor: "#fff", borderRadius: 8, padding: 12, borderWidth: 1, borderColor: "#e5e7eb" },
  cardRow: { flexDirection: "row", justifyContent: "space-between", alignItems: "center" },
  cardTitle: { fontSize: 15, fontWeight: "600", color: "#111827", flex: 1, marginRight: 8 },
  amount: { fontSize: 15, fontWeight: "700", color: "#2563eb" },
  bubble: { backgroundColor: "#fff", borderRadius: 10, padding: 10, borderWidth: 1, borderColor: "#e5e7eb" },
  bubbleSender: { fontSize: 12, fontWeight: "700", color: "#2563eb", marginBottom: 2 },
  bubbleBody: { fontSize: 14, color: "#374151" },
  msgRow: { flexDirection: "row", gap: 8, padding: 10, backgroundColor: "#fff", borderTopWidth: 1, borderTopColor: "#e5e7eb" },
});
