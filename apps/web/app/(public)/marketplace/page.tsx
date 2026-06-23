"use client";

import { useState } from "react";
import Link from "next/link";

const TRADES = [
  "All Trades",
  "Architect",
  "Civil Engineer",
  "Electrician",
  "Plumber",
  "Carpenter",
  "Painter",
  "Mason",
  "Structural Engineer",
  "Quantity Surveyor",
  "Interior Designer",
  "Landscaper",
  "HVAC Technician",
  "Welder",
];

type Professional = {
  id: number;
  name: string;
  title: string;
  location: string;
  rating: number;
  reviews: number;
  rate: string;
  initials: string;
  coverColor: string;
  verified: boolean;
  vetted: boolean;
  insured: boolean;
  skills: string[];
  available: boolean;
};

const ALL_PROS: Professional[] = [
  {
    id: 1,
    name: "James Mwangi",
    title: "Structural Engineer",
    location: "Nairobi, Kenya",
    rating: 4.9,
    reviews: 42,
    rate: "KES 8,500/day",
    initials: "JM",
    coverColor: "linear-gradient(135deg, #011D47 0%, #1a3560 100%)",
    verified: true,
    vetted: true,
    insured: true,
    skills: ["Structural Design", "AutoCAD", "Site Supervision"],
    available: true,
  },
  {
    id: 2,
    name: "Amina Hassan",
    title: "Interior Designer",
    location: "Mombasa, Kenya",
    rating: 4.8,
    reviews: 28,
    rate: "KES 6,000/day",
    initials: "AH",
    coverColor: "linear-gradient(135deg, #C43100 0%, #a02800 100%)",
    verified: true,
    vetted: false,
    insured: true,
    skills: ["Space Planning", "3D Rendering", "Procurement"],
    available: true,
  },
  {
    id: 3,
    name: "Peter Kamau",
    title: "Certified Electrician",
    location: "Kisumu, Kenya",
    rating: 4.7,
    reviews: 61,
    rate: "KES 3,500/day",
    initials: "PK",
    coverColor: "linear-gradient(135deg, #D4A017 0%, #b88a12 100%)",
    verified: true,
    vetted: true,
    insured: false,
    skills: ["Solar Installation", "Industrial Wiring", "Panel Boards"],
    available: false,
  },
  {
    id: 4,
    name: "Grace Wanjiku",
    title: "Quantity Surveyor",
    location: "Nairobi, Kenya",
    rating: 4.9,
    reviews: 19,
    rate: "KES 7,000/day",
    initials: "GW",
    coverColor: "linear-gradient(135deg, #1B5E20 0%, #2e7d32 100%)",
    verified: true,
    vetted: true,
    insured: true,
    skills: ["Cost Estimation", "BOQ Preparation", "Tender Management"],
    available: true,
  },
  {
    id: 5,
    name: "David Otieno",
    title: "Civil Engineer",
    location: "Eldoret, Kenya",
    rating: 4.6,
    reviews: 33,
    rate: "KES 9,000/day",
    initials: "DO",
    coverColor: "linear-gradient(135deg, #37474f 0%, #546e7a 100%)",
    verified: true,
    vetted: false,
    insured: true,
    skills: ["Road Design", "Drainage", "Project Management"],
    available: true,
  },
  {
    id: 6,
    name: "Fatuma Ali",
    title: "Architect",
    location: "Nairobi, Kenya",
    rating: 5.0,
    reviews: 11,
    rate: "KES 12,000/day",
    initials: "FA",
    coverColor: "linear-gradient(135deg, #4527A0 0%, #7b1fa2 100%)",
    verified: true,
    vetted: true,
    insured: true,
    skills: ["Residential Design", "Revit", "ArchiCAD"],
    available: true,
  },
];

function StarRating({ rating }: { rating: number }) {
  return (
    <div className="bl-pro-rating">
      {[1, 2, 3, 4, 5].map((i) => (
        <span
          key={i}
          className={i <= Math.round(rating) ? "bl-star" : "bl-star-empty"}
        >
          ★
        </span>
      ))}
      <span style={{ color: "var(--bl-muted)", fontSize: 11 }}>
        {rating.toFixed(1)}
      </span>
    </div>
  );
}

function ProCard({ pro }: { pro: Professional }) {
  return (
    <div className="bl-pro-card">
      <div className="bl-pro-card-cover" style={{ background: pro.coverColor }}>
        <div className="bl-pro-card-cover-overlay" />
        {!pro.available && (
          <span
            style={{
              position: "absolute",
              top: 10,
              right: 10,
              background: "rgba(0,0,0,.5)",
              color: "#fff",
              fontSize: 10,
              fontWeight: 600,
              padding: "2px 8px",
              borderRadius: 20,
            }}
          >
            Unavailable
          </span>
        )}
        <div className="bl-pro-avatar">{pro.initials}</div>
      </div>
      <div className="bl-pro-card-body">
        <div className="bl-pro-name">{pro.name}</div>
        <div className="bl-pro-title">{pro.title}</div>
        <div className="bl-pro-location">
          <svg
            width="11"
            height="11"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            aria-hidden="true"
          >
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
            <circle cx="12" cy="10" r="3" />
          </svg>
          {pro.location}
        </div>
        <StarRating rating={pro.rating} />
        <span style={{ fontSize: 11, color: "var(--bl-muted)" }}>
          ({pro.reviews} reviews)
        </span>

        <div style={{ marginTop: 10 }}>
          <span className="bl-pro-rate">{pro.rate}</span>
        </div>

        <div className="bl-trust-badges">
          {pro.verified && (
            <span className="bl-trust-badge bl-trust-verified">NCA Verified</span>
          )}
          {pro.vetted && (
            <span className="bl-trust-badge bl-trust-vetted">bildfie Vetted</span>
          )}
          {pro.insured && (
            <span className="bl-trust-badge bl-trust-insured">Insured</span>
          )}
        </div>

        <div className="bl-pro-skills">
          {pro.skills.map((s) => (
            <span key={s} className="bl-skill-tag">
              {s}
            </span>
          ))}
        </div>

        <div style={{ marginTop: 14, display: "flex", gap: 8 }}>
          <Link
            href="/signup"
            className="bl-btn bl-btn-dark bl-btn-sm"
            style={{ flex: 1, justifyContent: "center" }}
          >
            View Profile
          </Link>
          <Link
            href="/signup"
            className="bl-btn bl-btn-outline bl-btn-sm"
            title="Send message"
            aria-label="Send message"
          >
            <svg
              width="13"
              height="13"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2"
              aria-hidden="true"
            >
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
          </Link>
        </div>
      </div>
    </div>
  );
}

export default function MarketplacePage() {
  const [search, setSearch] = useState("");
  const [trade, setTrade] = useState("All Trades");
  const [location, setLocation] = useState("");
  const [availableOnly, setAvailableOnly] = useState(false);
  const [verifiedOnly, setVerifiedOnly] = useState(false);

  const filtered = ALL_PROS.filter((pro) => {
    const matchSearch =
      !search ||
      pro.name.toLowerCase().includes(search.toLowerCase()) ||
      pro.title.toLowerCase().includes(search.toLowerCase()) ||
      pro.skills.some((s) =>
        s.toLowerCase().includes(search.toLowerCase())
      );
    const matchTrade =
      trade === "All Trades" ||
      pro.title.toLowerCase().includes(trade.toLowerCase());
    const matchLocation =
      !location ||
      pro.location.toLowerCase().includes(location.toLowerCase());
    const matchAvailable = !availableOnly || pro.available;
    const matchVerified = !verifiedOnly || pro.verified;
    return (
      matchSearch &&
      matchTrade &&
      matchLocation &&
      matchAvailable &&
      matchVerified
    );
  });

  return (
    <>
      {/* Search header */}
      <div
        style={{
          background: "var(--bl-bg)",
          borderBottom: "1px solid var(--bl-rule)",
          padding: "28px 0",
        }}
      >
        <div className="bl-container">
          <h1
            style={{
              fontFamily: "Playfair Display, Georgia, serif",
              fontSize: "1.7rem",
              marginBottom: 16,
            }}
          >
            Browse Professionals
          </h1>
          <div className="bl-search-bar">
            <input
              type="text"
              placeholder="Search by name, skill, or trade…"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              aria-label="Search professionals"
            />
            <select
              value={trade}
              onChange={(e) => setTrade(e.target.value)}
              aria-label="Select trade"
            >
              {TRADES.map((t) => (
                <option key={t} value={t}>
                  {t}
                </option>
              ))}
            </select>
            <button className="bl-btn bl-btn-dark" type="button">
              Search
            </button>
          </div>
        </div>
      </div>

      {/* Main layout */}
      <div className="bl-container" style={{ padding: "32px 24px" }}>
        <div
          style={{
            display: "grid",
            gridTemplateColumns: "240px 1fr",
            gap: 28,
            alignItems: "flex-start",
          }}
        >
          {/* Filter panel */}
          <div className="bl-filter-panel" style={{ position: "sticky", top: "calc(var(--bl-nav-h) + 16px)" }}>
            <div className="bl-filter-section">
              <div className="bl-filter-title">Trade / Category</div>
              <select
                className="bl-select"
                value={trade}
                onChange={(e) => setTrade(e.target.value)}
                aria-label="Filter by trade"
              >
                {TRADES.map((t) => (
                  <option key={t} value={t}>
                    {t}
                  </option>
                ))}
              </select>
            </div>

            <div className="bl-filter-section">
              <div className="bl-filter-title">Location</div>
              <input
                className="bl-input"
                type="text"
                placeholder="City or county…"
                value={location}
                onChange={(e) => setLocation(e.target.value)}
                aria-label="Filter by location"
              />
            </div>

            <div className="bl-filter-section">
              <div className="bl-filter-title">Availability</div>
              <label className="bl-checkbox-group">
                <input
                  type="checkbox"
                  className="bl-checkbox"
                  checked={availableOnly}
                  onChange={(e) => setAvailableOnly(e.target.checked)}
                />
                Available now only
              </label>
            </div>

            <div className="bl-filter-section">
              <div className="bl-filter-title">Verification</div>
              <label className="bl-checkbox-group">
                <input
                  type="checkbox"
                  className="bl-checkbox"
                  checked={verifiedOnly}
                  onChange={(e) => setVerifiedOnly(e.target.checked)}
                />
                NCA Verified only
              </label>
            </div>

            <div className="bl-filter-section">
              <div className="bl-filter-title">Day Rate (KES)</div>
              <div style={{ display: "flex", gap: 8 }}>
                <input
                  className="bl-input"
                  type="number"
                  placeholder="Min"
                  aria-label="Minimum day rate"
                />
                <input
                  className="bl-input"
                  type="number"
                  placeholder="Max"
                  aria-label="Maximum day rate"
                />
              </div>
            </div>

            <button
              className="bl-btn bl-btn-outline bl-btn-sm bl-btn-full"
              onClick={() => {
                setSearch("");
                setTrade("All Trades");
                setLocation("");
                setAvailableOnly(false);
                setVerifiedOnly(false);
              }}
            >
              Clear filters
            </button>
          </div>

          {/* Results */}
          <div>
            <div
              style={{
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                marginBottom: 20,
              }}
            >
              <span
                style={{ fontSize: 13, color: "var(--bl-muted)" }}
              >
                {filtered.length} professional{filtered.length !== 1 ? "s" : ""} found
              </span>
              <select className="bl-select" style={{ width: "auto", minWidth: 160 }} aria-label="Sort results">
                <option>Most relevant</option>
                <option>Highest rated</option>
                <option>Lowest rate</option>
                <option>Newest</option>
              </select>
            </div>

            {filtered.length === 0 ? (
              <div className="bl-empty">
                <div className="bl-empty-icon">🔍</div>
                <p>No professionals match your filters. Try broadening your search.</p>
                <button
                  className="bl-btn bl-btn-outline bl-btn-sm"
                  style={{ marginTop: 8 }}
                  onClick={() => {
                    setSearch("");
                    setTrade("All Trades");
                    setLocation("");
                    setAvailableOnly(false);
                    setVerifiedOnly(false);
                  }}
                >
                  Clear all filters
                </button>
              </div>
            ) : (
              <div className="bl-grid-3">
                {filtered.map((pro) => (
                  <ProCard key={pro.id} pro={pro} />
                ))}
              </div>
            )}

            {/* Pagination placeholder */}
            {filtered.length > 0 && (
              <div className="bl-pagination">
                <button className="bl-page-btn" disabled aria-label="Previous page">←</button>
                <button className="bl-page-btn active" aria-current="page">1</button>
                <button className="bl-page-btn" aria-label="Page 2">2</button>
                <button className="bl-page-btn" aria-label="Page 3">3</button>
                <span style={{ padding: "0 4px", color: "var(--bl-muted)" }}>…</span>
                <button className="bl-page-btn" aria-label="Next page">→</button>
              </div>
            )}
          </div>
        </div>
      </div>
    </>
  );
}
