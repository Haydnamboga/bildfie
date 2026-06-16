"use client";
import { useEffect, useState } from "react";
import { api } from "@/lib/api";
import { AppNav } from "@/components/AppNav";

type Offer = { id: string; status: string; message?: string; amount?: string; createdAt: string; fromUser?: { fullName: string }; toUser?: { fullName: string } };

export default function OffersPage() {
  const [sent, setSent] = useState<Offer[]>([]);
  const [received, setReceived] = useState<Offer[]>([]);

  useEffect(() => {
    api.myOffers().then((r: unknown) => {
      const data = r as { sent: Offer[]; received: Offer[] };
      setSent(data.sent ?? []);
      setReceived(data.received ?? []);
    }).catch(() => {});
  }, []);

  async function respond(id: string, action: "accept" | "decline") {
    if (action === "accept") await api.acceptOffer(id);
    else await api.declineOffer(id);
    setReceived(prev => prev.map(o => o.id === id ? { ...o, status: action === "accept" ? "ACCEPTED" : "DECLINED" } : o));
  }

  const statusBadge = (s: string) => (
    <span className={`badge badge-${s === "ACCEPTED" ? "green" : s === "PENDING" ? "yellow" : s === "DECLINED" ? "red" : "blue"}`}>{s}</span>
  );

  const OfferCard = ({ o, type }: { o: Offer; type: "sent" | "received" }) => (
    <div className="card">
      <div style={{ display: "flex", justifyContent: "space-between", marginBottom: 8 }}>
        <p style={{ fontWeight: 500 }}>{type === "sent" ? `To: ${o.toUser?.fullName}` : `From: ${o.fromUser?.fullName}`}</p>
        {statusBadge(o.status)}
      </div>
      {o.message && <p style={{ fontSize: 14, color: "#6b7280", marginBottom: 8 }}>{o.message}</p>}
      {o.amount && <p style={{ fontWeight: 600 }}>KES {Number(o.amount).toLocaleString()}</p>}
      {type === "received" && o.status === "PENDING" && (
        <div style={{ display: "flex", gap: 8, marginTop: 12 }}>
          <button className="btn btn-primary" onClick={() => respond(o.id, "accept")}>Accept</button>
          <button className="btn btn-danger" onClick={() => respond(o.id, "decline")}>Decline</button>
        </div>
      )}
    </div>
  );

  return (
    <>
      <AppNav />
      <div className="container" style={{ padding: "32px 20px" }}>
        <div className="page-header"><h1>Offers</h1></div>
        <div className="grid-2">
          <div>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 12 }}>Received ({received.length})</h2>
            {received.length === 0 ? <div className="card empty"><p>No offers received.</p></div> : received.map(o => <OfferCard key={o.id} o={o} type="received" />)}
          </div>
          <div>
            <h2 style={{ fontSize: 16, fontWeight: 600, marginBottom: 12 }}>Sent ({sent.length})</h2>
            {sent.length === 0 ? <div className="card empty"><p>No offers sent.</p></div> : sent.map(o => <OfferCard key={o.id} o={o} type="sent" />)}
          </div>
        </div>
      </div>
    </>
  );
}
