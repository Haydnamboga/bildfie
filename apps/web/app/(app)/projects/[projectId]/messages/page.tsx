"use client";
import { use, useEffect, useRef, useState } from "react";
import { api } from "@/lib/api";
import { getCurrentUser } from "@/lib/auth";

type Message = { id: string; body: string; createdAt: string; author: { id: string; fullName: string } };

export default function MessagesPage({ params }: { params: Promise<{ projectId: string }> }) {
  const { projectId } = use(params);
  const me = getCurrentUser();
  const [messages, setMessages] = useState<Message[]>([]);
  const [text, setText] = useState("");
  const bottomRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    api.listMessages(projectId).then(r => setMessages((r as Message[]).reverse())).catch(() => {});
  }, [projectId]);

  useEffect(() => { bottomRef.current?.scrollIntoView({ behavior: "smooth" }); }, [messages]);

  async function send(e: React.FormEvent) {
    e.preventDefault();
    if (!text.trim()) return;
    const m = await api.sendMessage(projectId, text) as Message;
    setMessages(prev => [...prev, m]);
    setText("");
  }

  return (
    <>
      
      <div className="container" style={{ padding: "32px 20px" }}>
        <div className="page-header"><h1>Messages</h1></div>
        <div className="card" style={{ display: "flex", flexDirection: "column", height: "60vh" }}>
          <div style={{ flex: 1, overflowY: "auto", padding: "12px 0", display: "flex", flexDirection: "column", gap: 12 }}>
            {messages.map(m => {
              const isMe = m.author.id === me?.id;
              return (
                <div key={m.id} style={{ display: "flex", justifyContent: isMe ? "flex-end" : "flex-start" }}>
                  <div style={{ maxWidth: "70%", background: isMe ? "#2563eb" : "#f3f4f6", color: isMe ? "#fff" : "#111827", borderRadius: 12, padding: "8px 14px" }}>
                    {!isMe && <p style={{ fontSize: 12, fontWeight: 600, marginBottom: 2, opacity: 0.7 }}>{m.author.fullName}</p>}
                    <p style={{ fontSize: 14 }}>{m.body}</p>
                    <p style={{ fontSize: 11, opacity: 0.6, marginTop: 2, textAlign: "right" }}>{new Date(m.createdAt).toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })}</p>
                  </div>
                </div>
              );
            })}
            <div ref={bottomRef} />
          </div>
          <form onSubmit={send} style={{ borderTop: "1px solid #e5e7eb", paddingTop: 12, display: "flex", gap: 8 }}>
            <input placeholder="Type a message…" value={text} onChange={e => setText(e.target.value)} style={{ flex: 1 }} />
            <button type="submit" className="btn btn-primary">Send</button>
          </form>
        </div>
      </div>
    </>
  );
}
