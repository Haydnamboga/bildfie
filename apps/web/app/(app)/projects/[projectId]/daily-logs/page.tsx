"use client";
import { useEffect, useState } from "react";
import { useParams } from "next/navigation";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type DailyLog = {
  id: string;
  logDate: string;
  weather?: string;
  workersCount?: number;
  notes: string;
  createdAt: string;
};

function todayDate(): string {
  return new Date().toISOString().split("T")[0];
}

export default function DailyLogsPage() {
  const { projectId } = useParams<{ projectId: string }>();

  const [logs, setLogs] = useState<DailyLog[]>([]);
  const [loading, setLoading] = useState(true);
  const [adding, setAdding] = useState(false);
  const [form, setForm] = useState({
    logDate: todayDate(),
    weather: "",
    workersCount: "",
    notes: "",
  });
  const [formError, setFormError] = useState("");

  useEffect(() => {
    api.listDailyLogs(projectId)
      .then(data => setLogs((data as DailyLog[]).sort((a, b) => new Date(b.logDate).getTime() - new Date(a.logDate).getTime())))
      .catch(() => {})
      .finally(() => setLoading(false));
  }, [projectId]);

  async function addLog(e: React.FormEvent) {
    e.preventDefault();
    setFormError("");
    try {
      const body: Record<string, unknown> = {
        logDate: form.logDate,
        notes: form.notes,
      };
      if (form.weather) body.weather = form.weather;
      if (form.workersCount) body.workersCount = Number(form.workersCount);
      const log = await api.createDailyLog(projectId, body as Parameters<typeof api.createDailyLog>[1]) as DailyLog;
      setLogs(prev => [log, ...prev]);
      setForm({ logDate: todayDate(), weather: "", workersCount: "", notes: "" });
      setAdding(false);
    } catch (err: unknown) {
      setFormError((err as { message?: string }).message ?? "Failed to add log.");
    }
  }

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 24 }}>
          <div className="page-header" style={{ marginBottom: 0 }}>
            <h1>Daily Logs</h1>
          </div>
          <button className="btn btn-primary" onClick={() => setAdding(c => !c)}>+ Add Log</button>
        </div>

        {adding && (
          <div className="card" style={{ marginBottom: 24 }}>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 16 }}>Add Daily Log</h2>
            <form onSubmit={addLog} style={{ display: "flex", flexDirection: "column", gap: 12 }}>
              <div style={{ display: "flex", gap: 12 }}>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Date</label>
                  <input
                    type="date"
                    value={form.logDate}
                    onChange={e => setForm(f => ({ ...f, logDate: e.target.value }))}
                    required
                  />
                </div>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Weather (optional)</label>
                  <input
                    placeholder="e.g. Sunny, 28°C"
                    value={form.weather}
                    onChange={e => setForm(f => ({ ...f, weather: e.target.value }))}
                  />
                </div>
                <div className="form-group" style={{ flex: 1 }}>
                  <label className="label">Workers on site (optional)</label>
                  <input
                    type="number"
                    placeholder="0"
                    value={form.workersCount}
                    onChange={e => setForm(f => ({ ...f, workersCount: e.target.value }))}
                    min={0}
                  />
                </div>
              </div>
              <div className="form-group">
                <label className="label">Notes</label>
                <textarea
                  placeholder="What happened on site today…"
                  value={form.notes}
                  onChange={e => setForm(f => ({ ...f, notes: e.target.value }))}
                  rows={4}
                  required
                />
              </div>
              {formError && <p className="error">{formError}</p>}
              <div style={{ display: "flex", gap: 8 }}>
                <button type="submit" className="btn btn-primary">Save Log</button>
                <button type="button" className="btn btn-secondary" onClick={() => setAdding(false)}>Cancel</button>
              </div>
            </form>
          </div>
        )}

        {loading ? (
          <div className="card empty"><p>Loading…</p></div>
        ) : logs.length === 0 ? (
          <div className="card empty"><p>No daily logs yet. Start tracking site activity!</p></div>
        ) : (
          <div style={{ display: "flex", flexDirection: "column", gap: 12 }}>
            {logs.map(log => (
              <div key={log.id} className="card">
                <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center", marginBottom: 8 }}>
                  <h3 style={{ fontWeight: 600 }}>{new Date(log.logDate).toLocaleDateString("en-GB", { weekday: "long", year: "numeric", month: "long", day: "numeric" })}</h3>
                  <div style={{ display: "flex", gap: 12, fontSize: 13, color: "#6b7280" }}>
                    {log.weather && <span>🌤 {log.weather}</span>}
                    {log.workersCount != null && <span>👷 {log.workersCount} workers</span>}
                  </div>
                </div>
                <p style={{ fontSize: 14, color: "#374151", lineHeight: 1.6, whiteSpace: "pre-wrap" }}>{log.notes}</p>
              </div>
            ))}
          </div>
        )}
      </div>
    </>
  );
}
