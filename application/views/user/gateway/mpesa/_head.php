<?php /* SHULETECH: shared <head> for the M-Pesa / choose-payment pages */ ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo html_escape($title); ?> · <?php echo html_escape($school); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap">
<link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/lucide-static@0.543.0/lucide.css">
<script>(function(){var t=null;try{t=localStorage.getItem("st-theme")}catch(e){}if(t!=="light"){document.documentElement.classList.add("st-dark")}})();</script>
<style>
  :root { --bg:#fff; --fg:#0a0a0a; --card:#fff; --muted:#f5f5f5; --mfg:#737373; --border:#e5e5e5; --input:#e5e5e5; --primary:#171717; --pfg:#fafafa; --ring:#a1a1a1; --red:#e7000b; --green:#16a34a; --mpesa:#16a34a; --mpesa-tint:#f0fdf4; --blue:#2563eb; --blue-tint:#eff6ff; color-scheme: light; }
  html.st-dark { --bg:#0f0f10; --fg:#fafafa; --card:#18181a; --muted:#232325; --mfg:#a1a1a1; --border:rgba(255,255,255,.09); --input:rgba(255,255,255,.13); --primary:#e5e5e5; --pfg:#171717; --ring:#737373; --red:#ff6467; --green:#4ade80; --mpesa:#4ade80; --mpesa-tint:rgba(34,197,94,.14); --blue:#60a5fa; --blue-tint:rgba(59,130,246,.15); color-scheme: dark; }
  * { box-sizing: border-box; }
  body { margin: 0; background: var(--bg); color: var(--fg); font: 14.5px/1.5 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif; -webkit-font-smoothing: antialiased; }
  .wrap { max-width: 480px; margin: 0 auto; padding: 32px 16px 48px; }
  .top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 26px; }
  .back { color: var(--mfg); text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 13.5px; }
  .back:hover { color: var(--fg); }
  .school { color: var(--mfg); font-size: 13px; }
  h1 { font-size: 24px; font-weight: 700; letter-spacing: -.02em; margin: 0 0 4px; }
  .sub { color: var(--mfg); margin: 0 0 22px; }
  .card { background: var(--card); border: 1px solid var(--border); border-radius: 14px; padding: 18px 20px; margin-bottom: 16px; }
  .lines { margin: 0; padding: 0; list-style: none; }
  .lines li { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; font-size: 13.5px; }
  .lines li span:first-child { color: var(--mfg); }
  .lines li.total { border-top: 1px solid var(--border); margin-top: 6px; padding-top: 12px; font-weight: 700; font-size: 16px; }
  .lines li.total span:first-child { color: var(--fg); }
  label.fl { display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; }
  .phone { position: relative; }
  .phone i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--mfg); }
  input.in { width: 100%; height: 44px; border: 1px solid var(--input); border-radius: 10px; background: transparent; color: var(--fg); font: inherit; font-size: 15.5px; padding: 0 14px 0 40px; letter-spacing: .02em; }
  html.st-dark input.in { background: rgba(255,255,255,.035); }
  input.in:focus { outline: 0; border-color: var(--ring); box-shadow: 0 0 0 3px color-mix(in srgb, var(--ring) 35%, transparent); }
  .hint { color: var(--mfg); font-size: 12.5px; margin-top: 6px; }
  .btn { width: 100%; height: 44px; border: 0; border-radius: 10px; background: var(--primary); color: var(--pfg); font: inherit; font-weight: 600; font-size: 15px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 14px; }
  .btn:disabled { opacity: .6; cursor: default; }
  .btn.mpesa { background: var(--mpesa); color: #fff; }
  html.st-dark .btn.mpesa { color: #052e16; }
  .alert { border-radius: 10px; padding: 10px 14px; font-size: 13.5px; margin-top: 14px; display: none; border: 1px solid var(--border); }
  .alert.err { display: block; color: var(--red); border-color: color-mix(in srgb, var(--red) 35%, transparent); background: color-mix(in srgb, var(--red) 8%, transparent); }
  .alert.info { display: block; color: var(--fg); background: var(--muted); }
  .wait { text-align: center; padding: 10px 0 4px; display: none; }
  .wait .ring { width: 54px; height: 54px; border-radius: 50%; border: 3px solid var(--muted); border-top-color: var(--mpesa); margin: 6px auto 14px; animation: spin .9s linear infinite; }
  .wait h2 { font-size: 17px; margin: 0 0 4px; }
  .wait p { color: var(--mfg); margin: 0; font-size: 13.5px; }
  .linkbtn { background: none; border: 0; color: var(--mfg); font: inherit; font-size: 13px; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; margin-top: 12px; }
  .badge { display: inline-block; font-size: 11.5px; font-weight: 600; padding: 2px 8px; border-radius: 6px; background: var(--muted); color: var(--mfg); margin-left: 6px; vertical-align: middle; }
  .opt { display: flex; align-items: center; gap: 14px; padding: 16px 18px; border: 1px solid var(--border); border-radius: 14px; text-decoration: none; color: var(--fg); background: var(--card); margin-bottom: 12px; transition: border-color .15s, background .15s; }
  .opt:hover { border-color: color-mix(in srgb, var(--fg) 25%, transparent); }
  .opt .ic { width: 44px; height: 44px; border-radius: 11px; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; flex: none; }
  .opt .t { font-weight: 600; font-size: 15px; }
  .opt .d { color: var(--mfg); font-size: 13px; }
  .opt .go { margin-left: auto; color: var(--mfg); }
  .secure { color: var(--mfg); font-size: 12.5px; display: flex; align-items: center; gap: 6px; justify-content: center; margin-top: 18px; }
  @keyframes spin { to { transform: rotate(360deg); } }
</style>
