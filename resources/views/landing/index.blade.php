@php
    $brand = config('brand');
    $logoUrl = asset($brand['logo']);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $brand['short_name'] }} | {{ $brand['full_name'] }}</title>
  <meta name="description" content="{{ $brand['description'] }}">
  <link rel="icon" type="image/png" href="{{ $logoUrl }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Carlito:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <style>
    /* ─── VARIABLES ─────────────────────────────────────────── */
    :root{
      --navy-deep:#04091a;--navy:#060e1e;--navy-mid:#0d1f3c;--navy-light:#132848;
      --navy-card:rgba(13,31,60,0.75);--teal:#00c9a7;--teal-dark:#009f85;
      --teal-glow:rgba(0,201,167,0.12);--teal-border:rgba(0,201,167,0.2);
      --gold:#f0a500;--gold-glow:rgba(240,165,0,0.12);
      --white:#f0f6ff;--muted:#7a90b0;--muted-light:#a8bcd4;
      --radius-sm:8px;--radius-md:16px;--radius-lg:24px;
      --transition:0.3s cubic-bezier(0.4,0,0.2,1);
      --shadow-sm:0 4px 20px rgba(0,0,0,0.3);
      --shadow-md:0 12px 40px rgba(0,0,0,0.4);
      --shadow-lg:0 24px 80px rgba(0,0,0,0.5);
      --shadow-teal:0 0 30px rgba(0,201,167,0.2);
      --font:"Carlito","Calibri",sans-serif;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    html{scroll-behavior:smooth;}
    body{font-family:var(--font);background:var(--navy);color:var(--white);overflow-x:hidden;line-height:1.65;}
    a{text-decoration:none;color:inherit;}ul{list-style:none;}
    .container{max-width:1200px;margin:0 auto;padding:0 24px;}
    .section-tag{display:inline-flex;align-items:center;gap:8px;background:var(--teal-glow);border:1px solid var(--teal-border);color:var(--teal);font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;padding:6px 16px;border-radius:100px;margin-bottom:20px;}
    .section-tag::before{content:"";width:6px;height:6px;background:var(--teal);border-radius:50%;}
    .section-title{font-family:var(--font);font-size:clamp(1.9rem,4vw,2.9rem);font-weight:700;line-height:1.2;margin-bottom:20px;}
    .section-title span{background:linear-gradient(135deg,var(--teal),var(--gold));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .section-subtitle{font-size:1.05rem;color:var(--muted-light);max-width:580px;line-height:1.75;}

    /* ─── CURSOR ─────────────────────────────────────────────── */
    .cursor-dot{width:8px;height:8px;background:var(--teal);border-radius:50%;position:fixed;pointer-events:none;z-index:10000;transform:translate(-50%,-50%);}
    .cursor-ring{width:32px;height:32px;border:1.5px solid rgba(0,201,167,.5);border-radius:50%;position:fixed;pointer-events:none;z-index:9999;transform:translate(-50%,-50%);transition:width .3s,height .3s,opacity .3s;}
    @media(max-width:768px){.cursor-dot,.cursor-ring{display:none;}}

    /* ─── TOP INFO BAR ───────────────────────────────────────── */
    .topbar{position:fixed;top:0;left:0;right:0;height:42px;z-index:1001;background:linear-gradient(90deg,#010610,#071426,#010610);border-bottom:1px solid rgba(0,201,167,0.1);display:flex;align-items:center;}
    .topbar-inner{max-width:1200px;margin:0 auto;padding:0 24px;width:100%;display:flex;align-items:center;justify-content:space-between;}
    .topbar-left,.topbar-right{display:flex;align-items:center;gap:22px;}
    .topbar-item{display:flex;align-items:center;gap:7px;font-size:.78rem;color:var(--muted-light);transition:var(--transition);}
    .topbar-item:hover{color:var(--teal);}
    .topbar-item i{color:var(--teal);font-size:.72rem;}
    .topbar-divider{width:1px;height:18px;background:rgba(255,255,255,.08);}
    .topbar-social{display:flex;align-items:center;gap:7px;}
    .topbar-social a{width:24px;height:24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:5px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.65rem;transition:var(--transition);}
    .topbar-social a:hover{background:var(--teal-glow);border-color:var(--teal);color:var(--teal);}
    @media(max-width:900px){.topbar-addr{display:none;}}
    @media(max-width:600px){.topbar-email{display:none;}}
    @media(max-width:480px){.topbar{display:none;}}

    /* ─── NAVIGATION ─────────────────────────────────────────── */
    nav{position:fixed;top:42px;left:0;right:0;z-index:1000;padding:0 24px;transition:var(--transition);}
    nav.scrolled{background:rgba(6,14,30,.94);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);box-shadow:0 2px 20px rgba(0,0,0,0.35);}
    .nav-inner{max-width:1200px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;height:72px;}
    .logo-wrap{display:flex;align-items:center;gap:11px;cursor:pointer;}
    .logo-text-wrap{display:flex;flex-direction:column;line-height:1;}
    .logo-name{font-family:var(--font);font-size:1.3rem;font-weight:700;color:var(--white);}
    .logo-name span{color:var(--teal);}
    .logo-sub{font-size:.58rem;font-weight:700;color:var(--muted);letter-spacing:.18em;text-transform:uppercase;margin-top:3px;}
    .nav-links{display:flex;align-items:center;gap:2px;}
    .nav-links a{font-size:.9rem;font-weight:700;color:var(--muted-light);padding:8px 13px;border-radius:var(--radius-sm);transition:var(--transition);}
    .nav-links a:hover,.nav-links a.active{color:var(--teal);background:var(--teal-glow);}
    .nav-cta{background:var(--teal)!important;color:var(--navy-deep)!important;font-weight:700!important;padding:10px 22px!important;border-radius:100px!important;box-shadow:0 4px 16px rgba(0,201,167,.3);}
    .nav-cta:hover{background:var(--teal-dark)!important;transform:translateY(-1px);}
    .hamburger{display:none;flex-direction:column;gap:5px;cursor:pointer;padding:8px;border:none;background:transparent;}
    .hamburger span{display:block;width:24px;height:2px;background:var(--white);border-radius:2px;transition:var(--transition);}
    .hamburger.open span:nth-child(1){transform:rotate(45deg) translate(5px,5px);}
    .hamburger.open span:nth-child(2){opacity:0;}
    .hamburger.open span:nth-child(3){transform:rotate(-45deg) translate(5px,-5px);}
    .mobile-menu{position:fixed;top:114px;left:0;right:0;background:rgba(6,14,30,.98);backdrop-filter:blur(20px);border-bottom:1px solid var(--teal-border);padding:20px 24px 28px;z-index:999;transform:translateY(-115%);transition:transform .4s cubic-bezier(.4,0,.2,1);}
    .mobile-menu.open{transform:translateY(0);}
    .mobile-menu a{display:block;font-size:1rem;font-weight:700;color:var(--muted-light);padding:13px 0;border-bottom:1px solid rgba(255,255,255,.05);transition:var(--transition);}
    .mobile-menu a:hover{color:var(--teal);padding-left:8px;}
    .mob-cta{margin-top:18px;background:var(--teal);color:var(--navy-deep)!important;font-weight:700!important;text-align:center;padding:14px!important;border-radius:100px;border-bottom:none!important;}
    @media(max-width:768px){
      nav{top:0;}
      .mobile-menu{top:72px;}
    }
    @media(max-width:480px){
      nav{top:0!important;}
      .mobile-menu{top:72px!important;}
    }

    /* ─── HERO ───────────────────────────────────────────────── */
    #hero{position:relative;min-height:100vh;display:flex;align-items:center;overflow:hidden;padding-top:114px;}
    .hero-grid{position:absolute;inset:0;background-image:linear-gradient(rgba(0,201,167,.055) 1px,transparent 1px),linear-gradient(90deg,rgba(0,201,167,.055) 1px,transparent 1px);background-size:60px 60px;mask-image:radial-gradient(ellipse 80% 80% at 50% 50%,black 30%,transparent 80%);}
    .orb{position:absolute;border-radius:50%;filter:blur(80px);pointer-events:none;animation:orbFloat 9s ease-in-out infinite;}
    .orb-1{width:520px;height:520px;background:radial-gradient(circle,rgba(0,201,167,.16) 0%,transparent 70%);top:-120px;right:-80px;}
    .orb-2{width:400px;height:400px;background:radial-gradient(circle,rgba(240,165,0,.1) 0%,transparent 70%);bottom:-40px;left:-80px;animation-delay:-3.5s;}
    .orb-3{width:280px;height:280px;background:radial-gradient(circle,rgba(0,80,220,.07) 0%,transparent 70%);top:35%;left:42%;animation-delay:-6s;}
    @keyframes orbFloat{0%,100%{transform:translate(0,0) scale(1);}33%{transform:translate(18px,-18px) scale(1.04);}66%{transform:translate(-14px,14px) scale(.96);}}
    .hero-content{position:relative;z-index:2;max-width:1200px;margin:0 auto;padding:80px 24px;display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;}
    .hero-badge{display:inline-flex;align-items:center;gap:8px;background:var(--teal-glow);border:1px solid var(--teal-border);color:var(--teal);font-size:.78rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:8px 18px;border-radius:100px;margin-bottom:28px;animation:fadeUp .6s ease both;}
    .pulse-dot{width:8px;height:8px;background:var(--teal);border-radius:50%;animation:pulse 2s ease infinite;}
    @keyframes pulse{0%,100%{box-shadow:0 0 0 0 rgba(0,201,167,.5);}50%{box-shadow:0 0 0 8px rgba(0,201,167,0);}}
    .hero-title{font-family:var(--font);font-size:clamp(2.6rem,5vw,4.2rem);font-weight:700;line-height:1.08;margin-bottom:24px;animation:fadeUp .6s .1s ease both;}
    .hero-title .brand{color:var(--teal);}
    .hero-title .accent{color:var(--gold);}
    @keyframes fadeUp{from{opacity:0;transform:translateY(28px);}to{opacity:1;transform:translateY(0);}}
    .hero-desc{font-size:1.05rem;color:var(--muted-light);line-height:1.78;max-width:510px;margin-bottom:38px;animation:fadeUp .6s .2s ease both;}
    .hero-btns{display:flex;gap:14px;flex-wrap:wrap;animation:fadeUp .6s .3s ease both;}
    .btn-p{display:inline-flex;align-items:center;gap:10px;background:var(--teal);color:var(--navy-deep);font-family:var(--font);font-size:.95rem;font-weight:700;padding:15px 30px;border-radius:100px;border:none;cursor:pointer;transition:var(--transition);box-shadow:0 4px 20px rgba(0,201,167,.3);}
    .btn-p:hover{background:var(--teal-dark);transform:translateY(-3px);box-shadow:0 8px 30px rgba(0,201,167,.4);}
    .btn-s{display:inline-flex;align-items:center;gap:10px;background:transparent;color:var(--white);font-family:var(--font);font-size:.95rem;font-weight:700;padding:14px 28px;border-radius:100px;border:1.5px solid rgba(255,255,255,.2);cursor:pointer;transition:var(--transition);}
    .btn-s:hover{border-color:var(--teal);color:var(--teal);background:var(--teal-glow);transform:translateY(-3px);}
    .hero-stats{display:flex;gap:30px;margin-top:48px;animation:fadeUp .6s .4s ease both;flex-wrap:wrap;}
    .stat-item{border-left:2px solid var(--teal-border);padding-left:18px;}
    .stat-num{font-family:var(--font);font-size:1.75rem;font-weight:700;color:var(--teal);}
    .stat-lbl{font-size:.78rem;color:var(--muted);margin-top:3px;}
    /* Hero Right — Dashboard Mockup */
    .hero-right{position:relative;animation:fadeRight .8s .2s ease both;}
    @keyframes fadeRight{from{opacity:0;transform:translateX(36px);}to{opacity:1;transform:translateX(0);}}
    .dash{background:var(--navy-card);border:1px solid var(--teal-border);border-radius:var(--radius-lg);padding:26px;box-shadow:var(--shadow-lg),var(--shadow-teal);backdrop-filter:blur(20px);}
    .dash-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;}
    .dash-dots{display:flex;gap:6px;}
    .dash-dots span{width:10px;height:10px;border-radius:50%;}
    .dash-dots span:nth-child(1){background:#ff5f57;}.dash-dots span:nth-child(2){background:#febc2e;}.dash-dots span:nth-child(3){background:#28c840;}
    .dash-label{font-size:.82rem;color:var(--muted);}
    .dash-kpis{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:22px;}
    .kpi{background:rgba(0,201,167,.06);border:1px solid rgba(0,201,167,.1);border-radius:10px;padding:12px 10px;}
    .kpi-l{font-size:.68rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:5px;}
    .kpi-v{font-family:var(--font);font-size:1.2rem;font-weight:700;color:var(--teal);}
    .dash-chart{height:78px;display:flex;align-items:flex-end;gap:7px;margin-bottom:18px;}
    .bar{flex:1;background:linear-gradient(180deg,var(--teal),rgba(0,201,167,.18));border-radius:4px 4px 0 0;animation:growB 1s ease both;}
    @keyframes growB{from{transform:scaleY(0);transform-origin:bottom;}to{transform:scaleY(1);transform-origin:bottom;}}
    .dash-rows{display:flex;flex-direction:column;gap:9px;}
    .drow{display:flex;align-items:center;justify-content:space-between;padding:9px 12px;background:rgba(255,255,255,.03);border-radius:8px;}
    .drow-l{display:flex;align-items:center;gap:9px;}
    .drow-ic{width:26px;height:26px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:.72rem;}
    .drow-t{font-size:.78rem;color:var(--muted-light);}
    .drow-v{font-size:.83rem;font-weight:700;}
    .pos{color:var(--teal);}.neg{color:#ff6b6b;}.gld{color:var(--gold);}
    .fbadge{position:absolute;background:rgba(6,14,30,.92);border:1px solid var(--teal-border);border-radius:12px;padding:11px 16px;backdrop-filter:blur(10px);animation:floatY 4s ease-in-out infinite;z-index:3;}
    .fbadge-1{top:-18px;left:-28px;}
    .fbadge-2{bottom:18px;right:-28px;animation-delay:-2s;}
    @keyframes floatY{0%,100%{transform:translateY(0);}50%{transform:translateY(-9px);}}
    .fbadge-inner{display:flex;align-items:center;gap:8px;}
    .fbadge-ic{width:30px;height:30px;background:var(--teal-glow);border:1px solid var(--teal-border);border-radius:7px;display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:.82rem;}
    .fbadge-t{font-size:.73rem;}
    .fbadge-lbl{color:var(--muted);margin-bottom:2px;}
    .fbadge-val{font-weight:700;color:var(--white);}

    /* ─── ABOUT ───────────────────────────────────────────────── */
    #about{padding:120px 0;background:linear-gradient(180deg,var(--navy),var(--navy-mid) 50%,var(--navy));}
    .about-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;}
    .about-card{background:var(--navy-card);border:1px solid var(--teal-border);border-radius:var(--radius-lg);padding:40px;backdrop-filter:blur(20px);box-shadow:var(--shadow-md),var(--shadow-teal);position:relative;}
    .a-icon{width:60px;height:60px;background:linear-gradient(135deg,var(--teal-glow),var(--gold-glow));border:1px solid var(--teal-border);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:1.5rem;margin-bottom:22px;}
    .a-title{font-family:var(--font);font-size:1.15rem;font-weight:700;margin-bottom:12px;}
    .a-text{font-size:.9rem;color:var(--muted-light);line-height:1.75;}
    .a-metrics{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:24px;}
    .a-metric{background:rgba(0,201,167,.06);border:1px solid rgba(0,201,167,.1);border-radius:var(--radius-sm);padding:14px;text-align:center;}
    .a-metric-n{font-family:var(--font);font-size:1.7rem;font-weight:700;color:var(--teal);}
    .a-metric-l{font-size:.72rem;color:var(--muted);margin-top:4px;}
    .aside-card{position:absolute;background:var(--navy-mid);border:1px solid var(--teal-border);border-radius:var(--radius-md);padding:14px 18px;backdrop-filter:blur(10px);}
    .aside-1{top:-18px;right:-18px;animation:floatY 5s ease-in-out infinite;}
    .aside-2{bottom:-18px;left:-18px;animation:floatY 5s ease-in-out infinite;animation-delay:-2.5s;}
    .aside-ic{color:var(--gold);font-size:1.1rem;margin-bottom:5px;}
    .aside-t{font-size:.78rem;font-weight:700;color:var(--white);}
    .aside-s{font-size:.68rem;color:var(--muted);}
    .about-list{margin-top:32px;display:flex;flex-direction:column;gap:16px;}
    .alist-item{display:flex;gap:14px;align-items:flex-start;}
    .alist-chk{width:26px;height:26px;background:var(--teal-glow);border:1px solid var(--teal-border);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:.72rem;flex-shrink:0;margin-top:2px;}
    .alist-h{font-family:var(--font);font-size:.93rem;font-weight:700;margin-bottom:4px;}
    .alist-p{font-size:.84rem;color:var(--muted-light);line-height:1.65;}

    /* ─── FEATURES ────────────────────────────────────────────── */
    #features{padding:120px 0;position:relative;overflow:hidden;}
    #features::before{content:"";position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(0,201,167,.04) 0%,transparent 70%);top:50%;left:50%;transform:translate(-50%,-50%);pointer-events:none;}
    .feat-hdr{text-align:center;margin-bottom:68px;}
    .feat-hdr .section-subtitle{margin:0 auto;}
    .feat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;}
    .feat-card{background:var(--navy-card);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-lg);padding:34px 30px;backdrop-filter:blur(20px);transition:var(--transition);position:relative;overflow:hidden;cursor:default;}
    .feat-card::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,var(--teal-glow),transparent 60%);opacity:0;transition:var(--transition);}
    .feat-card:hover{border-color:var(--teal-border);transform:translateY(-8px);box-shadow:var(--shadow-md),var(--shadow-teal);}
    .feat-card:hover::before{opacity:1;}
    .feat-card.hl{border-color:var(--teal-border);background:linear-gradient(135deg,rgba(0,201,167,.08),var(--navy-card));}
    .feat-num{position:absolute;top:22px;right:22px;font-size:2.4rem;font-weight:700;color:rgba(255,255,255,.03);}
    .feat-ic{width:54px;height:54px;background:var(--teal-glow);border:1px solid var(--teal-border);border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;margin-bottom:22px;transition:var(--transition);position:relative;z-index:1;}
    .feat-ic i{font-size:1.3rem;color:var(--teal);}
    .feat-card:hover .feat-ic{background:var(--teal);border-color:var(--teal);box-shadow:0 0 20px rgba(0,201,167,.4);}
    .feat-card:hover .feat-ic i{color:var(--navy-deep);}
    .feat-title{font-family:var(--font);font-size:1.02rem;font-weight:700;margin-bottom:11px;position:relative;z-index:1;}
    .feat-desc{font-size:.855rem;color:var(--muted-light);line-height:1.72;position:relative;z-index:1;}
    .feat-last{grid-column:1 / -1;max-width:390px;margin:0 auto;width:100%;}

    /* ─── PRICING ─────────────────────────────────────────────── */
    #pricing{padding:120px 0;background:linear-gradient(180deg,var(--navy),var(--navy-mid) 50%,var(--navy));}
    .price-hdr{text-align:center;margin-bottom:48px;}
    .price-toggle{display:flex;align-items:center;justify-content:center;gap:14px;margin-bottom:56px;}
    .tgl-lbl{font-size:.93rem;font-weight:700;color:var(--muted-light);transition:var(--transition);}
    .tgl-lbl.on{color:var(--teal);}
    .tgl-sw{width:54px;height:28px;background:var(--navy-light);border:1px solid var(--teal-border);border-radius:100px;cursor:pointer;position:relative;}
    .tgl-thumb{width:20px;height:20px;background:var(--teal);border-radius:50%;position:absolute;top:3px;left:4px;transition:var(--transition);box-shadow:0 0 10px rgba(0,201,167,.4);}
    .tgl-sw.yr .tgl-thumb{left:calc(100% - 24px);}
    .save-pill{background:linear-gradient(135deg,var(--gold),#ffbe45);color:var(--navy-deep);font-size:.68rem;font-weight:700;padding:3px 10px;border-radius:100px;}
    .price-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:22px;align-items:stretch;}
    .pc{background:var(--navy-card);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-lg);padding:38px 30px;backdrop-filter:blur(20px);transition:var(--transition);display:flex;flex-direction:column;position:relative;overflow:hidden;}
    .pc::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,transparent,var(--teal),transparent);opacity:0;transition:var(--transition);}
    .pc.feat-p{border-color:var(--teal-border);background:linear-gradient(160deg,rgba(0,201,167,.09),var(--navy-card) 50%);transform:translateY(-12px);box-shadow:var(--shadow-md),var(--shadow-teal);}
    .pc.feat-p::before{opacity:1;}
    .pc:hover:not(.feat-p){border-color:var(--teal-border);transform:translateY(-6px);}
    .pc:hover::before{opacity:1;}
    .pop-badge{position:absolute;top:18px;right:18px;background:linear-gradient(135deg,var(--teal),var(--teal-dark));color:var(--navy-deep);font-size:.68rem;font-weight:700;padding:4px 12px;border-radius:100px;letter-spacing:.06em;text-transform:uppercase;}
    .plan-n{font-family:var(--font);font-size:.95rem;font-weight:700;color:var(--muted-light);text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px;}
    .plan-tag{font-size:.855rem;color:var(--muted);margin-bottom:26px;line-height:1.55;}
    .plan-price{margin-bottom:30px;}
    .price-amt{font-family:var(--font);font-size:2.5rem;font-weight:700;line-height:1;}
    .price-cur{font-size:1.1rem;font-weight:700;color:var(--muted-light);vertical-align:top;padding-top:5px;display:inline-block;}
    .price-per{font-size:.83rem;color:var(--muted);margin-top:5px;}
    .p-m{display:block;}.p-y{display:none;}
    .price-grid.yr .p-m{display:none;}.price-grid.yr .p-y{display:block;}
    .plan-feats{flex:1;display:flex;flex-direction:column;gap:11px;margin-bottom:28px;}
    .pf{display:flex;align-items:center;gap:9px;font-size:.855rem;color:var(--muted-light);}
    .pf i{color:var(--teal);font-size:.78rem;flex-shrink:0;}
    .pf.off{opacity:.38;}.pf.off i{color:var(--muted);}
    .btn-plan{display:block;width:100%;text-align:center;padding:13px;border-radius:100px;font-family:var(--font);font-size:.9rem;font-weight:700;cursor:pointer;transition:var(--transition);border:none;}
    .btn-out{background:transparent;border:1.5px solid var(--teal-border);color:var(--teal);}
    .btn-out:hover{background:var(--teal-glow);border-color:var(--teal);}
    .btn-sol{background:linear-gradient(135deg,var(--teal),var(--teal-dark));color:var(--navy-deep);box-shadow:0 4px 20px rgba(0,201,167,.3);}
    .btn-sol:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,201,167,.4);}

    /* ─── INTEGRATION ─────────────────────────────────────────── */
    #integration{padding:120px 0;overflow:hidden;}
    .integ-grid{display:grid;grid-template-columns:1fr 1fr;gap:80px;align-items:center;}
    .hub-wrap{display:flex;justify-content:center;}
    .hub{width:290px;height:290px;position:relative;}
    .hub-center{width:96px;height:96px;background:linear-gradient(135deg,var(--teal-glow),rgba(240,165,0,.1));border:2px solid var(--teal-border);border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;align-items:center;justify-content:center;z-index:2;}
    .hub-lbl{font-family:var(--font);font-size:.68rem;font-weight:700;color:var(--teal);text-align:center;line-height:1.35;}
    .hub-ring{width:252px;height:252px;border:1px dashed rgba(0,201,167,.2);border-radius:50%;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);animation:spin 22s linear infinite;}
    @keyframes spin{from{transform:translate(-50%,-50%) rotate(0deg);}to{transform:translate(-50%,-50%) rotate(360deg);}}
    .hub-node{width:62px;height:62px;background:var(--navy-mid);border:1px solid var(--teal-border);border-radius:12px;position:absolute;display:flex;flex-direction:column;align-items:center;justify-content:center;font-size:.62rem;font-weight:700;color:var(--muted-light);gap:3px;transition:var(--transition);}
    .hub-node:hover{border-color:var(--teal);background:var(--teal-glow);color:var(--teal);}
    .hub-node i{font-size:1.05rem;color:var(--teal);}
    .hub-n1{top:0;left:50%;transform:translateX(-50%);}
    .hub-n2{top:50%;right:-10px;transform:translateY(-50%);}
    .hub-n3{bottom:0;left:50%;transform:translateX(-50%);}
    .hub-n4{top:50%;left:-10px;transform:translateY(-50%);}
    .integ-cards{display:flex;flex-direction:column;gap:18px;margin-top:38px;}
    .ic{display:flex;gap:18px;align-items:flex-start;background:var(--navy-card);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-md);padding:22px;backdrop-filter:blur(10px);transition:var(--transition);}
    .ic:hover{border-color:var(--teal-border);transform:translateX(8px);}
    .ic-icon{width:46px;height:46px;background:var(--teal-glow);border:1px solid var(--teal-border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:1.05rem;flex-shrink:0;}
    .ic-title{font-family:var(--font);font-size:.93rem;font-weight:700;margin-bottom:5px;}
    .ic-desc{font-size:.835rem;color:var(--muted-light);line-height:1.65;}

    /* ─── WHY US ──────────────────────────────────────────────── */
    #why{padding:120px 0;background:linear-gradient(180deg,var(--navy),var(--navy-mid) 50%,var(--navy));}
    .why-hdr{text-align:center;margin-bottom:68px;}
    .why-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:18px;}
    .wc{background:var(--navy-card);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-lg);padding:30px 22px;text-align:center;backdrop-filter:blur(20px);transition:var(--transition);position:relative;overflow:hidden;}
    .wc::after{content:"";position:absolute;bottom:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--teal),var(--gold));transform:scaleX(0);transition:var(--transition);transform-origin:left;}
    .wc:hover{border-color:var(--teal-border);transform:translateY(-8px);box-shadow:var(--shadow-md);}
    .wc:hover::after{transform:scaleX(1);}
    .wc-ic{width:58px;height:58px;background:var(--teal-glow);border:1px solid var(--teal-border);border-radius:50%;display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:1.3rem;margin:0 auto 18px;transition:var(--transition);}
    .wc:hover .wc-ic{background:var(--teal);color:var(--navy-deep);box-shadow:0 0 20px rgba(0,201,167,.4);}
    .wc-title{font-family:var(--font);font-size:.93rem;font-weight:700;margin-bottom:9px;}
    .wc-desc{font-size:.78rem;color:var(--muted);line-height:1.62;}

    /* ─── CONTACT ─────────────────────────────────────────────── */
    #contact{padding:120px 0;position:relative;overflow:hidden;}
    #contact::before{content:"";position:absolute;width:480px;height:480px;background:radial-gradient(circle,rgba(240,165,0,.045) 0%,transparent 70%);bottom:-80px;right:-80px;pointer-events:none;}
    .con-hdr{text-align:center;margin-bottom:68px;}
    .con-grid{display:grid;grid-template-columns:1fr 1.5fr;gap:58px;}
    .con-cards{display:flex;flex-direction:column;gap:18px;}
    .cc{display:flex;gap:14px;align-items:flex-start;background:var(--navy-card);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-md);padding:18px 22px;transition:var(--transition);}
    .cc:hover{border-color:var(--teal-border);}
    .cc-ic{width:42px;height:42px;background:var(--teal-glow);border:1px solid var(--teal-border);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--teal);font-size:.95rem;flex-shrink:0;}
    .cc-lbl{font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;}
    .cc-val{font-size:.88rem;font-weight:700;color:var(--white);}
    .form-wrap{background:var(--navy-card);border:1px solid rgba(255,255,255,.06);border-radius:var(--radius-lg);padding:38px;backdrop-filter:blur(20px);}
    .form-h{font-family:var(--font);font-size:1.25rem;font-weight:700;margin-bottom:6px;}
    .form-sub{font-size:.855rem;color:var(--muted-light);margin-bottom:26px;}
    .f-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    .f-grp{margin-bottom:18px;}
    .f-lbl{display:block;font-size:.76rem;font-weight:700;color:var(--muted-light);text-transform:uppercase;letter-spacing:.06em;margin-bottom:7px;}
    .f-in,.f-sel,.f-ta{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:var(--radius-sm);color:var(--white);font-family:var(--font);font-size:.9rem;padding:11px 15px;outline:none;transition:var(--transition);}
    .f-in:focus,.f-sel:focus,.f-ta:focus{border-color:var(--teal);background:rgba(0,201,167,.05);box-shadow:0 0 0 3px rgba(0,201,167,.1);}
    .f-in::placeholder,.f-ta::placeholder{color:var(--muted);}
    .f-ta{height:114px;resize:vertical;}
    .f-sel option{background:var(--navy-mid);color:var(--white);}
    .btn-send{width:100%;padding:15px;background:linear-gradient(135deg,var(--teal),var(--teal-dark));color:var(--navy-deep);font-family:var(--font);font-size:.97rem;font-weight:700;border:none;border-radius:100px;cursor:pointer;transition:var(--transition);display:flex;align-items:center;justify-content:center;gap:10px;box-shadow:0 4px 20px rgba(0,201,167,.3);}
    .btn-send:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(0,201,167,.4);}
    .form-ok{display:none;text-align:center;padding:28px 16px;}
    .form-ok i{font-size:3rem;color:var(--teal);margin-bottom:14px;display:block;}
    .form-ok h3{font-family:var(--font);font-size:1.25rem;font-weight:700;margin-bottom:8px;}
    .form-ok p{color:var(--muted-light);font-size:.88rem;}

    /* ─── FOOTER ──────────────────────────────────────────────── */
    footer{background:var(--navy-deep);border-top:1px solid rgba(255,255,255,.06);padding:78px 0 30px;position:relative;z-index:2;}
    .ft-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:56px;margin-bottom:56px;}
    .ft-logo{display:flex;align-items:center;gap:10px;margin-bottom:18px;}
    .ft-desc{font-size:.855rem;color:var(--muted);line-height:1.78;max-width:295px;margin-bottom:26px;}
    .soc-links{display:flex;gap:10px;}
    .soc-a{width:38px;height:38px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:9px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.88rem;transition:var(--transition);}
    .soc-a:hover{background:var(--teal-glow);border-color:var(--teal);color:var(--teal);transform:translateY(-3px);}
    .ft-col-h{font-family:var(--font);font-size:.85rem;font-weight:700;color:var(--white);margin-bottom:18px;text-transform:uppercase;letter-spacing:.09em;}
    .ft-links{display:flex;flex-direction:column;gap:9px;}
    .ft-links a{font-size:.845rem;color:var(--muted);transition:var(--transition);}
    .ft-links a:hover{color:var(--teal);padding-left:5px;}
    .ft-bot{border-top:1px solid rgba(255,255,255,.06);padding-top:28px;display:flex;align-items:center;justify-content:space-between;}
    .ft-copy{font-size:.78rem;color:var(--muted);}
    .ft-copy span{color:var(--teal);}
    .ft-leg{display:flex;gap:22px;}
    .ft-leg a{font-size:.78rem;color:var(--muted);transition:var(--transition);}
    .ft-leg a:hover{color:var(--teal);}

    /* ─── SCROLL REVEAL ───────────────────────────────────────── */
    .rv{opacity:0;transform:translateY(38px);transition:opacity .7s ease,transform .7s ease;}
    .rv.vis{opacity:1;transform:translateY(0);}
    .rvl{opacity:0;transform:translateX(-38px);transition:opacity .7s ease,transform .7s ease;}
    .rvl.vis{opacity:1;transform:translateX(0);}
    .rvr{opacity:0;transform:translateX(38px);transition:opacity .7s ease,transform .7s ease;}
    .rvr.vis{opacity:1;transform:translateX(0);}
    .d1{transition-delay:.1s;}.d2{transition-delay:.2s;}.d3{transition-delay:.3s;}.d4{transition-delay:.4s;}.d5{transition-delay:.5s;}
    @media(prefers-reduced-motion:reduce){*{animation-duration:.01ms!important;transition-duration:.01ms!important;}.rv,.rvl,.rvr{opacity:1;transform:none;}}

    /* ─── RESPONSIVE ──────────────────────────────────────────── */
    @media(max-width:1100px){.why-grid{grid-template-columns:repeat(3,1fr);}.ft-grid{grid-template-columns:1fr 1fr;gap:38px;}}
    @media(max-width:900px){
      .hero-content{grid-template-columns:1fr;}.hero-right{display:none;}
      .about-grid{grid-template-columns:1fr;}.about-card{display:none;}
      .feat-grid{grid-template-columns:repeat(2,1fr);}
      .price-grid{grid-template-columns:1fr;max-width:420px;margin:0 auto;}
      .pc.feat-p{transform:none;}
      .integ-grid{grid-template-columns:1fr;}.hub-wrap{display:none;}
      .why-grid{grid-template-columns:repeat(2,1fr);}
      .con-grid{grid-template-columns:1fr;}
      nav .nav-links{display:none;}.hamburger{display:flex;}
      .hero-stats{flex-wrap:wrap;}
    }
    @media(max-width:600px){
      .feat-grid{grid-template-columns:1fr;}.why-grid{grid-template-columns:repeat(2,1fr);}
      .ft-grid{grid-template-columns:1fr;}.ft-bot{flex-direction:column;gap:14px;text-align:center;}
      .f-row{grid-template-columns:1fr;}.hero-btns{flex-direction:column;}
      .hero-title{font-size:2.3rem;}
    }
  </style>
</head>
<body>
  <div class="cursor-dot" id="cDot"></div>
  <div class="cursor-ring" id="cRing"></div>

  <!-- ═══════════════════════════════════════════════════════════
       TOP INFO BAR
  ═══════════════════════════════════════════════════════════ -->
  <div class="topbar">
    <div class="topbar-inner">
      <div class="topbar-left">
        <a href="tel:{{ preg_replace('/\s+/', '', $brand['phone']) }}" class="topbar-item">
          <i class="fas fa-phone"></i>
          <span>{{ $brand['phone'] }}</span>
        </a>
        <span class="topbar-divider"></span>
        <a href="mailto:{{ $brand['email'] }}" class="topbar-item topbar-email">
          <i class="fas fa-envelope"></i>
          <span>{{ $brand['email'] }}</span>
        </a>
      </div>
      <div class="topbar-right">
        <span class="topbar-item topbar-addr">
          <i class="fas fa-location-dot"></i>
          <span>{{ $brand['address'] }}</span>
        </span>
        <span class="topbar-divider topbar-addr"></span>
        <div class="topbar-social">
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════════
       NAVIGATION
  ═══════════════════════════════════════════════════════════ -->
  <nav id="navbar">
    <div class="nav-inner">
      <a href="#hero" class="logo-wrap">
        @include('landing.partials.logo-mark')
        <div class="logo-text-wrap">
          @php $nameParts = explode('-', $brand['short_name'], 2); @endphp
          <div class="logo-name">{{ $nameParts[0] ?? $brand['short_name'] }}@if(isset($nameParts[1]))-<span>{{ $nameParts[1] }}</span>@endif</div>
          <div class="logo-sub">{{ $brand['tagline'] }}</div>
        </div>
      </a>
      <ul class="nav-links">
        <li><a href="#about">About</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#pricing">Pricing</a></li>
        <li><a href="#integration">Integration</a></li>
        <li><a href="#contact">Contact</a></li>
        @auth
        <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
        @else
        <li><a href="{{ route('login') }}">Login</a></li>
        <li><a href="{{ route('register') }}" class="nav-cta">Get Started</a></li>
        @endauth
      </ul>
      <button class="hamburger" id="hbg" aria-label="Toggle menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <div class="mobile-menu" id="mobMenu">
    <a href="#about" onclick="closeMob()">About</a>
    <a href="#features" onclick="closeMob()">Features</a>
    <a href="#pricing" onclick="closeMob()">Pricing</a>
    <a href="#integration" onclick="closeMob()">Integration</a>
    <a href="#contact" onclick="closeMob()">Contact</a>
    @auth
    <a href="{{ route('dashboard') }}" class="mob-cta" onclick="closeMob()">Dashboard &rarr;</a>
    @else
    <a href="{{ route('login') }}" onclick="closeMob()">Login</a>
    <a href="{{ route('register') }}" class="mob-cta" onclick="closeMob()">Get Started &rarr;</a>
    @endauth
  </div>

  <!-- ═══════════════════════════════════════════════════════════
       HERO
  ═══════════════════════════════════════════════════════════ -->
  <section id="hero">
    <div class="hero-grid"></div>
    <div class="orb orb-1"></div><div class="orb orb-2"></div><div class="orb orb-3"></div>
    <div class="hero-content">
      <div class="hero-left">
        <div class="hero-badge"><div class="pulse-dot"></div>Pakistan's Premier Accounting Platform</div>
        <h1 class="hero-title">
          Smart Accounting<br>
          Powered by <span class="brand">G-TechX</span><br>
          for <span class="accent">Modern Business</span>
        </h1>
        <p class="hero-desc">Global TechX &amp; Accounting Solution delivers enterprise-grade financial management, FBR-integrated invoicing, and real-time reporting — purpose-built for Pakistani SMEs and corporate clients.</p>
        <div class="hero-btns">
          <a href="{{ auth()->check() ? route('dashboard') : route('register') }}" class="btn-p">Get Started <svg width="15" height="15" viewBox="0 0 16 16" fill="none"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          <a href="{{ route('pricing.page') }}" class="btn-s"><svg width="15" height="15" viewBox="0 0 16 16" fill="none"><rect x="2" y="3" width="12" height="10" rx="1.5" stroke="currentColor" stroke-width="1.5"/><path d="M5 7h6M5 10h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg> View Pricing</a>
        </div>
        <div class="hero-stats">
          <div class="stat-item"><div class="stat-num" data-count="500">0</div><div class="stat-lbl">Active Businesses</div></div>
          <div class="stat-item"><div class="stat-num" data-count="99">0</div><div class="stat-lbl">% Uptime SLA</div></div>
          <div class="stat-item"><div class="stat-num" data-count="24">0</div><div class="stat-lbl">/7 Support</div></div>
        </div>
      </div>
      <div class="hero-right">
        <div class="fbadge fbadge-1">
          <div class="fbadge-inner">
            <div class="fbadge-ic"><i class="fas fa-check-circle"></i></div>
            <div class="fbadge-t"><div class="fbadge-lbl">FBR Status</div><div class="fbadge-val">Compliant &#10003;</div></div>
          </div>
        </div>
        <div class="dash">
          <div class="dash-head">
            <div class="dash-dots"><span></span><span></span><span></span></div>
            <div class="dash-label">G-TechX Dashboard</div>
            <div style="font-size:.73rem;color:var(--teal);">&#9679; Live</div>
          </div>
          <div class="dash-kpis">
            <div class="kpi"><div class="kpi-l">Revenue</div><div class="kpi-v">&#8360;2.4M</div></div>
            <div class="kpi"><div class="kpi-l">Invoices</div><div class="kpi-v">148</div></div>
            <div class="kpi"><div class="kpi-l">Tax Filed</div><div class="kpi-v" style="color:var(--gold)">94%</div></div>
          </div>
          <div class="dash-chart">
            <div class="bar" style="height:45%;animation-delay:.1s"></div><div class="bar" style="height:65%;animation-delay:.15s"></div>
            <div class="bar" style="height:40%;animation-delay:.2s"></div><div class="bar" style="height:82%;animation-delay:.25s"></div>
            <div class="bar" style="height:55%;animation-delay:.3s"></div><div class="bar" style="height:92%;animation-delay:.35s"></div>
            <div class="bar" style="height:72%;animation-delay:.4s"></div>
          </div>
          <div class="dash-rows">
            <div class="drow"><div class="drow-l"><div class="drow-ic" style="background:rgba(0,201,167,.1);"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#00c9a7" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg></div><div class="drow-t">Invoice #INV-2024</div></div><div class="drow-v pos">+&#8360;45,000</div></div>
            <div class="drow"><div class="drow-l"><div class="drow-ic" style="background:rgba(240,165,0,.1);"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#f0a500" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></div><div class="drow-t">Tax Return Q4</div></div><div class="drow-v gld">Filed</div></div>
            <div class="drow"><div class="drow-l"><div class="drow-ic" style="background:rgba(100,150,255,.1);"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#6496ff" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div><div class="drow-t">Payroll &mdash; Dec</div></div><div class="drow-v neg">-&#8360;182,000</div></div>
          </div>
        </div>
        <div class="fbadge fbadge-2">
          <div class="fbadge-inner">
            <div class="fbadge-ic" style="background:var(--gold-glow);border-color:rgba(240,165,0,.3);"><i class="fas fa-shield-halved" style="color:var(--gold);"></i></div>
            <div class="fbadge-t"><div class="fbadge-lbl">Security</div><div class="fbadge-val">256-bit SSL</div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════
       ABOUT
  ═══════════════════════════════════════════════════════════ -->
  <section id="about">
    <div class="container">
      <div class="about-grid">
        <div class="rvl">
          <div class="about-card">
            <div class="a-icon"><i class="fas fa-globe-asia"></i></div>
            <div class="a-title">Global TechX &amp; Accounting Solution</div>
            <div class="a-text">Founded with a mission to modernize financial operations for Pakistani businesses, G-TechX bridges the gap between traditional accounting and cutting-edge digital solutions.</div>
            <div class="a-metrics">
              <div class="a-metric"><div class="a-metric-n" data-count="500">0</div><div class="a-metric-l">Businesses Served</div></div>
              <div class="a-metric"><div class="a-metric-n" data-count="99">0</div><div class="a-metric-l">% Satisfaction</div></div>
              <div class="a-metric"><div class="a-metric-n" data-count="5">0</div><div class="a-metric-l">Years Experience</div></div>
              <div class="a-metric"><div class="a-metric-n" data-count="24">0</div><div class="a-metric-l">/7 Support</div></div>
            </div>
            <div class="aside-card aside-1"><div class="aside-ic"><i class="fas fa-certificate"></i></div><div class="aside-t">FBR Certified</div><div class="aside-s">Government Approved</div></div>
            <div class="aside-card aside-2"><div class="aside-ic"><i class="fas fa-lock"></i></div><div class="aside-t">256-bit Encryption</div><div class="aside-s">Bank-Grade Security</div></div>
          </div>
        </div>
        <div class="rvr">
          <div class="section-tag">About Us</div>
          <h2 class="section-title">Modern Accounting for<br><span>Pakistan's Business Future</span></h2>
          <p class="section-subtitle">G-TechX provides an all-in-one accounting and business management platform purpose-built for the Pakistani market — seamlessly integrating with FBR and government compliance requirements.</p>
          <div class="about-list">
            <div class="alist-item"><div class="alist-chk"><i class="fas fa-check"></i></div><div><div class="alist-h">Comprehensive Account Management</div><div class="alist-p">Manage all company accounts, ledgers, and financial records from a single unified dashboard with full audit trails and real-time reporting.</div></div></div>
            <div class="alist-item"><div class="alist-chk"><i class="fas fa-check"></i></div><div><div class="alist-h">FBR &amp; Government Compliance</div><div class="alist-p">Fully integrated with Pakistan's FBR system — automate tax submissions, GST filing, and all regulatory compliance reports instantly.</div></div></div>
            <div class="alist-item"><div class="alist-chk"><i class="fas fa-check"></i></div><div><div class="alist-h">Subscription-Based Flexibility</div><div class="alist-p">Choose from monthly or yearly subscription plans tailored to the size and needs of your business — scale as you grow effortlessly.</div></div></div>
            <div class="alist-item"><div class="alist-chk"><i class="fas fa-check"></i></div><div><div class="alist-h">Cloud-Powered Infrastructure</div><div class="alist-p">Access your financial data securely from anywhere with real-time synchronization across all devices and team members.</div></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════
       FEATURES
  ═══════════════════════════════════════════════════════════ -->
  <section id="features">
    <div class="container">
      <div class="feat-hdr rv">
        <div class="section-tag">Features</div>
        <h2 class="section-title">Everything Your Business <span>Needs</span></h2>
        <p class="section-subtitle">Comprehensive accounting tools designed specifically for Pakistani businesses — from startups to enterprise corporations.</p>
      </div>
      <div class="feat-grid">
        <div class="feat-card hl rv d1"><div class="feat-num">01</div><div class="feat-ic"><i class="fas fa-building"></i></div><div class="feat-title">Company Account Management</div><div class="feat-desc">Manage multiple company accounts, subsidiaries, and branches with centralized control. Full chart of accounts, ledgers, journals, and automated trial balance.</div></div>
        <div class="feat-card rv d2"><div class="feat-num">02</div><div class="feat-ic"><i class="fas fa-file-invoice"></i></div><div class="feat-title">Invoice Management</div><div class="feat-desc">Create, send, and track professional invoices with automated reminders, payment tracking, and FBR-compliant billing templates for Pakistani businesses.</div></div>
        <div class="feat-card rv d3"><div class="feat-num">03</div><div class="feat-ic"><i class="fas fa-chart-pie"></i></div><div class="feat-title">Tax &amp; Financial Reports</div><div class="feat-desc">Auto-generate income tax returns, GST/Sales Tax reports, withholding statements, and P&amp;L statements compliant with Pakistan's tax laws.</div></div>
        <div class="feat-card rv d1"><div class="feat-num">04</div><div class="feat-ic"><i class="fas fa-landmark"></i></div><div class="feat-title">FBR Integration</div><div class="feat-desc">Direct API connection with Federal Board of Revenue. File e-returns, retrieve NTN data, verify STRN numbers, and automate sales tax submissions instantly.</div></div>
        <div class="feat-card rv d2"><div class="feat-num">05</div><div class="feat-ic"><i class="fas fa-shield-halved"></i></div><div class="feat-title">Government Compliance</div><div class="feat-desc">Stay audit-ready with automated SECP, FBR, and provincial tax authority compliance. Real-time alerts for upcoming regulatory deadline reminders.</div></div>
        <div class="feat-card rv d3"><div class="feat-num">06</div><div class="feat-ic"><i class="fas fa-cloud"></i></div><div class="feat-title">Secure Cloud-Based System</div><div class="feat-desc">Enterprise-grade cloud infrastructure with 256-bit SSL encryption, automatic backups every 6 hours, and 99.9% uptime SLA for your business continuity.</div></div>
        <div class="feat-card feat-last rv d1"><div class="feat-num">07</div><div class="feat-ic"><i class="fas fa-chart-line"></i></div><div class="feat-title">Real-Time Financial Tracking</div><div class="feat-desc">Live dashboards with real-time cash flow analysis, expense tracking, profit margins, and financial KPIs. Make data-driven decisions with instant actionable insights.</div></div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════
       PRICING
  ═══════════════════════════════════════════════════════════ -->
  <section id="pricing">
    <div class="container">
      <div class="price-hdr rv">
        <div class="section-tag">Pricing</div>
        <h2 class="section-title">Simple, Transparent <span>PKR Pricing</span></h2>
        <p class="section-subtitle" style="margin:0 auto;">No hidden fees. No surprises. Choose the plan that fits your business size and budget.</p>
      </div>
      <div class="price-toggle rv">
        <span class="tgl-lbl on" id="mLbl">Monthly</span>
        <div class="tgl-sw" id="tglSw" onclick="togglePrice()"><div class="tgl-thumb"></div></div>
        <span class="tgl-lbl" id="yLbl">Yearly</span>
        <span class="save-pill">Save 17%</span>
      </div>
      <div class="price-grid" id="pGrid">
        <div class="pc rv d1">
          <div class="plan-n">Starter</div>
          <div class="plan-tag">Perfect for freelancers and small businesses getting started with digital accounting.</div>
          <div class="plan-price">
            <div class="p-m"><div class="price-amt"><span class="price-cur">PKR </span>2,999</div><div class="price-per">per month, billed monthly</div></div>
            <div class="p-y"><div class="price-amt"><span class="price-cur">PKR </span>29,990</div><div class="price-per">per year &mdash; save PKR 5,998</div></div>
          </div>
          <ul class="plan-feats">
            <li class="pf"><i class="fas fa-check-circle"></i>1 Company Account</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Up to 100 Invoices/month</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Basic Financial Reports</li>
            <li class="pf"><i class="fas fa-check-circle"></i>FBR Tax Filing</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Email Support</li>
            <li class="pf"><i class="fas fa-check-circle"></i>5 GB Cloud Storage</li>
            <li class="pf off"><i class="fas fa-times-circle"></i>Multi-Branch Management</li>
            <li class="pf off"><i class="fas fa-times-circle"></i>Payroll Management</li>
          </ul>
          <a href="{{ route('register') }}" class="btn-plan btn-out">Subscribe Now</a>
        </div>
        <div class="pc feat-p rv d2">
          <div class="pop-badge">Most Popular</div>
          <div class="plan-n">Professional</div>
          <div class="plan-tag">Ideal for growing SMEs that need advanced accounting and compliance features.</div>
          <div class="plan-price">
            <div class="p-m"><div class="price-amt"><span class="price-cur">PKR </span>7,499</div><div class="price-per">per month, billed monthly</div></div>
            <div class="p-y"><div class="price-amt"><span class="price-cur">PKR </span>74,990</div><div class="price-per">per year &mdash; save PKR 14,998</div></div>
          </div>
          <ul class="plan-feats">
            <li class="pf"><i class="fas fa-check-circle"></i>Up to 5 Company Accounts</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Unlimited Invoices</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Advanced Financial Reports</li>
            <li class="pf"><i class="fas fa-check-circle"></i>FBR + SECP Integration</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Priority Support 24/7</li>
            <li class="pf"><i class="fas fa-check-circle"></i>50 GB Cloud Storage</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Multi-Branch Management</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Payroll Management</li>
          </ul>
          <a href="{{ route('register') }}" class="btn-plan btn-sol">Subscribe Now</a>
        </div>
        <div class="pc rv d3">
          <div class="plan-n">Enterprise</div>
          <div class="plan-tag">Full-scale solution for corporate clients with complex accounting requirements.</div>
          <div class="plan-price">
            <div class="p-m"><div class="price-amt"><span class="price-cur">PKR </span>14,999</div><div class="price-per">per month, billed monthly</div></div>
            <div class="p-y"><div class="price-amt"><span class="price-cur">PKR </span>149,990</div><div class="price-per">per year &mdash; save PKR 29,998</div></div>
          </div>
          <ul class="plan-feats">
            <li class="pf"><i class="fas fa-check-circle"></i>Unlimited Company Accounts</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Unlimited Everything</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Custom Reports &amp; Dashboards</li>
            <li class="pf"><i class="fas fa-check-circle"></i>All Government Integrations</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Dedicated Account Manager</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Unlimited Cloud Storage</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Multi-Branch Management</li>
            <li class="pf"><i class="fas fa-check-circle"></i>Custom API Integration</li>
          </ul>
          <a href="{{ route('register') }}" class="btn-plan btn-out">Subscribe Now</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════
       INTEGRATION
  ═══════════════════════════════════════════════════════════ -->
  <section id="integration">
    <div class="container">
      <div class="integ-grid">
        <div class="rvl hub-wrap">
          <div class="hub">
            <div class="hub-ring"></div>
            <div class="hub-center"><div class="hub-lbl">G-TechX<br>Core</div></div>
            <div class="hub-node hub-n1"><i class="fas fa-landmark"></i>FBR</div>
            <div class="hub-node hub-n2"><i class="fas fa-university"></i>Banks</div>
            <div class="hub-node hub-n3"><i class="fas fa-building-columns"></i>SECP</div>
            <div class="hub-node hub-n4"><i class="fas fa-shield-halved"></i>SBP</div>
          </div>
        </div>
        <div class="rvr">
          <div class="section-tag">Integration</div>
          <h2 class="section-title">Built for <span>Pakistan's</span> Business Ecosystem</h2>
          <p class="section-subtitle">G-TechX is deeply integrated with Pakistan's financial and regulatory infrastructure, ensuring your business stays compliant and connected at all times.</p>
          <div class="integ-cards">
            <div class="ic rv d1"><div class="ic-icon"><i class="fas fa-landmark"></i></div><div><div class="ic-title">FBR Integration</div><div class="ic-desc">Direct API with Federal Board of Revenue. File sales tax returns, income tax, retrieve NTN/STRN data, and generate tax certificates automatically.</div></div></div>
            <div class="ic rv d2"><div class="ic-icon"><i class="fas fa-scroll"></i></div><div><div class="ic-title">Government of Pakistan Compliance</div><div class="ic-desc">SECP company filings, SBP banking regulations, and provincial tax authorities — all compliance requirements managed in one unified platform.</div></div></div>
            <div class="ic rv d3"><div class="ic-icon"><i class="fas fa-lock"></i></div><div><div class="ic-title">Secure &amp; Reliable Infrastructure</div><div class="ic-desc">ISO 27001-aligned security, redundant cloud servers in Pakistan, and 99.9% uptime guarantee for uninterrupted accounting operations.</div></div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════
       WHY CHOOSE US
  ═══════════════════════════════════════════════════════════ -->
  <section id="why">
    <div class="container">
      <div class="why-hdr rv">
        <div class="section-tag">Why G-TechX</div>
        <h2 class="section-title">The G-TechX <span>Advantage</span></h2>
        <p class="section-subtitle" style="margin:0 auto;">Five reasons why Pakistan's top businesses choose G-TechX for all their accounting and compliance needs.</p>
      </div>
      <div class="why-grid">
        <div class="wc rv d1"><div class="wc-ic"><i class="fas fa-bolt"></i></div><div class="wc-title">Fast &amp; Secure</div><div class="wc-desc">Lightning-fast performance with bank-grade encryption protecting every transaction and financial record 24/7.</div></div>
        <div class="wc rv d2"><div class="wc-ic"><i class="fas fa-hand-pointer"></i></div><div class="wc-title">Easy to Use</div><div class="wc-desc">Intuitive interface designed for non-technical users. Start managing your accounts in minutes, not days.</div></div>
        <div class="wc rv d3"><div class="wc-ic"><i class="fas fa-briefcase"></i></div><div class="wc-title">Business-Friendly</div><div class="wc-desc">Built around Pakistani business practices — supports Urdu, PKR currency, and local accounting standards natively.</div></div>
        <div class="wc rv d4"><div class="wc-ic"><i class="fas fa-headset"></i></div><div class="wc-title">Professional Support</div><div class="wc-desc">Dedicated accounting experts and technical support available 24/7 to resolve any issues and guide your team.</div></div>
        <div class="wc rv d5"><div class="wc-ic"><i class="fas fa-award"></i></div><div class="wc-title">Trusted Platform</div><div class="wc-desc">Trusted by 500+ Pakistani businesses — from SMEs to corporate enterprises managing millions in transactions monthly.</div></div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════
       CONTACT
  ═══════════════════════════════════════════════════════════ -->
  <section id="contact">
    <div class="container">
      <div class="con-hdr rv">
        <div class="section-tag">Contact</div>
        <h2 class="section-title">Get in <span>Touch</span></h2>
        <p class="section-subtitle" style="margin:0 auto;">Have questions? Our team is ready to help you set up the perfect accounting solution for your business.</p>
      </div>
      <div class="con-grid">
        <div class="rvl">
          <div class="con-cards">
            <div class="cc"><div class="cc-ic"><i class="fas fa-phone"></i></div><div><div class="cc-lbl">Phone</div><div class="cc-val">{{ $brand['phone'] }}</div></div></div>
            <div class="cc"><div class="cc-ic"><i class="fas fa-envelope"></i></div><div><div class="cc-lbl">Email</div><div class="cc-val">{{ $brand['email'] }}</div></div></div>
            <div class="cc"><div class="cc-ic"><i class="fas fa-location-dot"></i></div><div><div class="cc-lbl">Office Address</div><div class="cc-val">{{ $brand['office'] }}</div></div></div>
            <div class="cc"><div class="cc-ic"><i class="fas fa-clock"></i></div><div><div class="cc-lbl">Business Hours</div><div class="cc-val">Mon &ndash; Sat: 9:00 AM &ndash; 6:00 PM PKT</div></div></div>
          </div>
        </div>
        <div class="rvr">
          <div class="form-wrap">
            <div id="formContent">
              <div class="form-h">Send us a Message</div>
              <div class="form-sub">Fill out the form and we'll get back to you within 24 hours.</div>
              <form id="cForm" onsubmit="sendForm(event)">
                <div class="f-row">
                  <div class="f-grp"><label class="f-lbl">First Name</label><input type="text" class="f-in" placeholder="Your first name" required></div>
                  <div class="f-grp"><label class="f-lbl">Last Name</label><input type="text" class="f-in" placeholder="Your last name" required></div>
                </div>
                <div class="f-grp"><label class="f-lbl">Business Email</label><input type="email" class="f-in" placeholder="you@company.com" required></div>
                <div class="f-grp"><label class="f-lbl">Phone Number</label><input type="tel" class="f-in" placeholder="+92 300 0000000"></div>
                <div class="f-grp">
                  <label class="f-lbl">Business Type</label>
                  <select class="f-sel f-in">
                    <option value="" disabled selected>Select your business type</option>
                    <option>Small Business (SME)</option><option>Medium Enterprise</option>
                    <option>Corporate / Large Enterprise</option><option>Freelancer / Sole Proprietor</option>
                    <option>Non-Profit Organization</option>
                  </select>
                </div>
                <div class="f-grp"><label class="f-lbl">Message</label><textarea class="f-ta" placeholder="Tell us about your accounting needs..."></textarea></div>
                <button type="submit" class="btn-send"><span>Send Message</span><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2L11 13M22 2L15 22 11 13 2 9l20-7z"/></svg></button>
              </form>
            </div>
            <div class="form-ok" id="formOk">
              <i class="fas fa-circle-check"></i>
              <h3>Message Sent!</h3>
              <p>Thank you for reaching out. Our team will get back to you within 24 hours.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════════
       FOOTER
  ═══════════════════════════════════════════════════════════ -->
  <footer>
    <div class="container">
      <div class="ft-grid">
        <div>
          <div class="ft-logo">
            @include('landing.partials.logo-mark')
            <div class="logo-text-wrap">
              <div class="logo-name">{{ $nameParts[0] ?? $brand['short_name'] }}@if(isset($nameParts[1]))-<span>{{ $nameParts[1] }}</span>@endif</div>
              <div class="logo-sub">{{ $brand['tagline'] }}</div>
            </div>
          </div>
          <p class="ft-desc">{{ $brand['full_name'] }} &mdash; Pakistan's most trusted accounting software. FBR-integrated, cloud-powered, and built for business growth.</p>
          <div class="soc-links">
            <a href="#" class="soc-a" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="soc-a" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
            <a href="#" class="soc-a" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
            <a href="#" class="soc-a" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" class="soc-a" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
        <div>
          <div class="ft-col-h">Solutions</div>
          <ul class="ft-links">
            <li><a href="#features">Account Management</a></li>
            <li><a href="#features">Invoice System</a></li>
            <li><a href="#features">Tax &amp; Reports</a></li>
            <li><a href="#features">FBR Integration</a></li>
            <li><a href="#features">Payroll Management</a></li>
          </ul>
        </div>
        <div>
          <div class="ft-col-h">Company</div>
          <ul class="ft-links">
            <li><a href="#about">About G-TechX</a></li>
            <li><a href="#pricing">Pricing Plans</a></li>
            <li><a href="#integration">Integrations</a></li>
            <li><a href="#contact">Contact Us</a></li>
            <li><a href="#">Blog</a></li>
          </ul>
        </div>
        <div>
          <div class="ft-col-h">Support</div>
          <ul class="ft-links">
            <li><a href="#">Documentation</a></li>
            <li><a href="#">Help Center</a></li>
            <li><a href="#">API Reference</a></li>
            <li><a href="#">System Status</a></li>
            <li><a href="#contact">Live Chat</a></li>
          </ul>
        </div>
      </div>
      <div class="ft-bot">
        <div class="ft-copy">&copy; {{ date('Y') }} <span>{{ $brand['copyright'] }}</span>. All rights reserved. Pakistan</div>
        <div class="ft-leg"><a href="#">Privacy Policy</a><a href="#">Terms of Service</a><a href="#">Cookie Policy</a></div>
      </div>
    </div>
  </footer>

  <!-- ═══════════════════════════════════════════════════════════
       JAVASCRIPT
  ═══════════════════════════════════════════════════════════ -->
  <script>
    // Custom Cursor
    const cDot=document.getElementById("cDot"),cRing=document.getElementById("cRing");
    let mx=0,my=0,rx=0,ry=0;
    document.addEventListener("mousemove",e=>{mx=e.clientX;my=e.clientY;cDot.style.left=mx+"px";cDot.style.top=my+"px";});
    (function loop(){rx+=(mx-rx)*.15;ry+=(my-ry)*.15;cRing.style.left=rx+"px";cRing.style.top=ry+"px";requestAnimationFrame(loop);})();
    document.querySelectorAll("a,button,.feat-card,.pc,.wc").forEach(el=>{
      el.addEventListener("mouseenter",()=>{cRing.style.width="48px";cRing.style.height="48px";cRing.style.opacity="0.8";});
      el.addEventListener("mouseleave",()=>{cRing.style.width="32px";cRing.style.height="32px";cRing.style.opacity="0.6";});
    });

    // Navbar
    const nav=document.getElementById("navbar");
    window.addEventListener("scroll",()=>{nav.classList.toggle("scrolled",window.scrollY>50);updateNav();});
    function updateNav(){
      const ss=document.querySelectorAll("section[id]"),ls=document.querySelectorAll(".nav-links a");
      let cur="";
      ss.forEach(s=>{if(window.scrollY>=s.offsetTop-100)cur=s.id;});
      ls.forEach(l=>{l.classList.remove("active");if(l.getAttribute("href")==="#"+cur)l.classList.add("active");});
    }

    // Mobile Menu
    const hbg=document.getElementById("hbg"),mob=document.getElementById("mobMenu");
    hbg.addEventListener("click",()=>{hbg.classList.toggle("open");mob.classList.toggle("open");});
    function closeMob(){hbg.classList.remove("open");mob.classList.remove("open");}

    // Scroll Reveal
    const rvEls=document.querySelectorAll(".rv,.rvl,.rvr");
    const rvObs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add("vis");rvObs.unobserve(e.target);}});},{threshold:.1,rootMargin:"0px 0px -50px 0px"});
    rvEls.forEach(e=>rvObs.observe(e));

    // Counter
    function countUp(el){
      const t=parseInt(el.dataset.count),suf=el.textContent.replace(/[0-9]/g,"");
      let c=0;const step=t/55;
      const id=setInterval(()=>{c=Math.min(c+step,t);el.textContent=Math.floor(c)+suf;if(c>=t){el.textContent=t+suf;clearInterval(id);}},16);
    }
    const ctObs=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting){countUp(e.target);ctObs.unobserve(e.target);}});},{threshold:.5});
    document.querySelectorAll("[data-count]").forEach(e=>ctObs.observe(e));

    // Pricing Toggle
    let isYr=false;
    function togglePrice(){
      isYr=!isYr;
      document.getElementById("tglSw").classList.toggle("yr",isYr);
      document.getElementById("pGrid").classList.toggle("yr",isYr);
      document.getElementById("mLbl").classList.toggle("on",!isYr);
      document.getElementById("yLbl").classList.toggle("on",isYr);
    }

    // Contact Form
    function sendForm(e){
      e.preventDefault();
      const btn=document.querySelector(".btn-send");
      btn.innerHTML='<i class="fas fa-spinner fa-spin"></i> Sending...';btn.disabled=true;
      setTimeout(()=>{document.getElementById("formContent").style.display="none";document.getElementById("formOk").style.display="block";},1400);
    }
    updateNav();
  </script>
</body>
</html>