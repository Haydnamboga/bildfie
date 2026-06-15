<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    {{-- Mobile first (spec §3 Rule 2 / §9): design for a 5-inch phone first. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b3d2e">
    <title>bildfie — Find and hire verified construction professionals</title>
    <meta name="description" content="Post a project. Contractors bid. Pay only when work is approved.">
    <style>
        :root {
            --green: #0b3d2e;
            --green-600: #0f5238;
            --accent: #f6b73c;
            --ink: #14201b;
            --muted: #5b6b63;
            --bg: #f7f9f8;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { min-height: 100%; display: flex; flex-direction: column; }
        header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 20px; max-width: 960px; margin: 0 auto; width: 100%;
        }
        .logo { font-size: 22px; font-weight: 800; color: var(--green); letter-spacing: -0.5px; }
        .logo span { color: var(--accent); }
        .nav-login { color: var(--green); text-decoration: none; font-weight: 600; font-size: 15px; }
        main {
            flex: 1; display: flex; flex-direction: column; justify-content: center;
            padding: 32px 20px 56px; max-width: 720px; margin: 0 auto; width: 100%; text-align: center;
        }
        h1 {
            font-size: clamp(28px, 7vw, 44px); line-height: 1.15; color: var(--green);
            letter-spacing: -1px; margin-bottom: 16px;
        }
        .sub { font-size: clamp(16px, 4.5vw, 20px); color: var(--muted); margin-bottom: 32px; }
        .cta { display: flex; flex-direction: column; gap: 12px; }
        .btn {
            display: block; padding: 16px 20px; border-radius: 12px; font-size: 17px;
            font-weight: 700; text-decoration: none; text-align: center; transition: transform .05s ease;
        }
        .btn:active { transform: scale(.98); }
        .btn-primary { background: var(--green); color: #fff; }
        .btn-secondary { background: #fff; color: var(--green); border: 2px solid var(--green); }
        .how { margin-top: 44px; display: grid; gap: 18px; text-align: left; }
        .step { background: #fff; border: 1px solid #e6ece9; border-radius: 14px; padding: 18px 20px; }
        .step b { color: var(--green); display: block; margin-bottom: 4px; }
        .step p { color: var(--muted); font-size: 15px; }
        footer { text-align: center; color: var(--muted); font-size: 13px; padding: 22px 20px 30px; }
        @media (min-width: 640px) {
            .cta { flex-direction: row; justify-content: center; }
            .btn { min-width: 220px; }
            .how { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header>
            <div class="logo">bild<span>fie</span></div>
            <a class="nav-login" href="{{ url('/login') }}">Log in</a>
        </header>

        <main>
            {{-- Headline + sub-headline copy is fixed by spec §2.3. Do not add stats or activity feeds. --}}
            <h1>Find and hire verified construction professionals.</h1>
            <p class="sub">Post a project. Contractors bid. Pay only when work is approved.</p>

            <div class="cta">
                <a class="btn btn-primary" href="{{ url('/post-a-project') }}">Post a Project</a>
                <a class="btn btn-secondary" href="{{ url('/join') }}">Join as a Professional</a>
            </div>

            <div class="how">
                <div class="step">
                    <b>1. Post</b>
                    <p>Describe the job and your fixed budget. Verified contractors in your area see it.</p>
                </div>
                <div class="step">
                    <b>2. Choose &amp; fund</b>
                    <p>Compare bids, pick one, and fund escrow by M-Pesa. Money is held safely.</p>
                </div>
                <div class="step">
                    <b>3. Approve &amp; pay</b>
                    <p>Work is done with photo proof. You approve, payment releases automatically.</p>
                </div>
            </div>
        </main>

        <footer>
            &copy; {{ date('Y') }} bildfie. Built in Kenya.
        </footer>
    </div>
</body>
</html>
