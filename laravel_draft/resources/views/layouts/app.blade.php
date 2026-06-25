<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('page_title', 'Colony Billing')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#eef4fb;
            --panel:rgba(255,255,255,.72);
            --panel-strong:rgba(255,255,255,.84);
            --panel-soft:rgba(248,251,255,.78);
            --line:rgba(191,208,230,.55);
            --line-strong:rgba(170,190,216,.72);
            --text:#16304a;
            --heading:#10263d;
            --muted:#647a92;
            --brand:#6d9fdb;
            --brand-strong:#4f84c7;
            --brand-soft:#e7f0fb;
            --success:#73ab99;
            --warn:#d4b07a;
            --danger:#cf929b;
            --shadow-soft:8px 8px 20px rgba(189,203,222,.34), -8px -8px 18px rgba(255,255,255,.9);
            --shadow-panel:16px 16px 34px rgba(188,201,220,.32), -12px -12px 28px rgba(255,255,255,.94);
            --shadow-inset:inset 2px 2px 4px rgba(255,255,255,.86), inset -3px -3px 7px rgba(196,208,226,.26);
            --radius-sm:12px;
            --radius-md:20px;
            --radius-lg:28px;
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            background:
                radial-gradient(circle at top left, rgba(255,255,255,.96), transparent 24%),
                radial-gradient(circle at top right, rgba(205,225,247,.46), transparent 26%),
                linear-gradient(180deg, #f8fbff 0%, var(--bg) 46%, #edf3fa 100%);
            background-attachment:fixed;
            color:var(--text);
            font-family:"Inter","Manrope",Segoe UI,Arial,sans-serif;
        }
        .app{display:flex;min-height:100vh}
        .sidebar{
            width:272px;
            background:linear-gradient(180deg, rgba(248,251,255,.74), rgba(238,245,252,.62));
            border-right:1px solid rgba(255,255,255,.82);
            padding:18px 14px;
            position:sticky;
            top:0;
            height:100vh;
            overflow:auto;
            box-shadow:var(--shadow-soft);
            backdrop-filter:blur(18px);
        }
        .brand{
            font-weight:900;
            font-size:17px;
            letter-spacing:.2px;
            padding:12px 12px 8px;
            color:var(--heading);
        }
        .sub{
            font-size:11px;
            color:var(--muted);
            padding:0 12px 14px;
            border-bottom:1px solid rgba(191,208,230,.5);
            letter-spacing:.8px;
            text-transform:uppercase;
        }
        .nav-section{margin-top:14px}
        .nav-head{font-size:11px;color:#7a92ad;text-transform:uppercase;letter-spacing:.9px;padding:8px 10px;font-weight:800}
        .nav a{
            display:flex;
            align-items:center;
            color:#3e5e7f;
            text-decoration:none;
            padding:10px 12px;
            border-radius:16px;
            margin:6px 0;
            font-size:13px;
            font-weight:700;
            border:1px solid transparent;
            background:linear-gradient(180deg, rgba(255,255,255,.72), rgba(239,245,252,.58));
            box-shadow:var(--shadow-soft);
            transition:transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease;
        }
        .nav a:hover{
            transform:translateY(-1px);
            border-color:rgba(109,159,219,.28);
            background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(232,242,252,.72));
            color:var(--heading);
        }
        .nav a.active{
            transform:translateY(-1px);
            border-color:rgba(109,159,219,.34);
            background:linear-gradient(90deg, rgba(231,240,251,.96), rgba(214,229,246,.84));
            color:var(--heading);
            box-shadow:var(--shadow-panel);
        }
        .nav-ico{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:24px;
            height:24px;
            margin-right:10px;
            border-radius:8px;
            background:linear-gradient(180deg, rgba(255,255,255,.9), rgba(228,238,248,.7));
            border:1px solid rgba(255,255,255,.82);
            box-shadow:var(--shadow-soft);
            font-size:13px;
            flex:none;
        }
        .main{flex:1;display:flex;flex-direction:column;min-width:0;position:relative}
        .top{
            background:linear-gradient(180deg, rgba(255,255,255,.84), rgba(241,247,253,.74));
            backdrop-filter:blur(18px);
            border-bottom:1px solid rgba(255,255,255,.82);
            padding:14px 24px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:var(--shadow-soft);
        }
        .top-title{font-weight:800;font-size:15px;letter-spacing:.3px;color:var(--heading)}
        .user{font-size:12px;color:var(--muted)}
        .container{padding:22px 24px;position:relative;z-index:1}
        .page-head{
            display:flex;
            justify-content:space-between;
            align-items:flex-end;
            gap:12px;
            margin-bottom:18px;
            padding:16px 18px;
            border:1px solid rgba(255,255,255,.82);
            border-radius:20px;
            background:linear-gradient(180deg, rgba(255,255,255,.8), rgba(240,246,252,.7));
            backdrop-filter:blur(14px);
            box-shadow:var(--shadow-panel);
        }
        .page-title{margin:0;font-size:22px;line-height:1.2;color:var(--heading)}
        .page-sub{margin:4px 0 0;color:var(--muted);font-size:13px}
        .crumb{font-size:12px;color:#7f96b1;text-transform:uppercase;letter-spacing:.7px}
        .grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:14px}
        .col-3{grid-column:span 3}.col-4{grid-column:span 4}.col-5{grid-column:span 5}.col-6{grid-column:span 6}.col-7{grid-column:span 7}.col-8{grid-column:span 8}.col-12{grid-column:span 12}
        .card{
            background:linear-gradient(180deg, rgba(255,255,255,.8), rgba(241,247,253,.68));
            border:1px solid rgba(255,255,255,.82);
            border-radius:18px;
            padding:15px;
            box-shadow:var(--shadow-panel);
            backdrop-filter:blur(12px);
            color:var(--text);
        }
        .card.soft{background:linear-gradient(180deg, rgba(249,252,255,.84), rgba(236,244,251,.7))}
        .section-title{margin:0 0 10px;font-size:16px;color:var(--heading)}
        .kpi{font-size:28px;font-weight:800;margin:8px 0 4px;color:var(--heading)}
        .muted{color:var(--muted);font-size:12px}
        .badge{display:inline-block;padding:3px 9px;border-radius:999px;font-size:11px;border:1px solid rgba(109,159,219,.24);background:rgba(231,240,251,.85);color:#456a8d;box-shadow:var(--shadow-soft)}
        .badge.success{border-color:rgba(115,171,153,.28);background:rgba(233,246,240,.92);color:#417061}
        .badge.warn{border-color:rgba(212,176,122,.28);background:rgba(255,247,234,.94);color:#8a6730}
        .banner{padding:10px 12px;border-radius:12px;background:linear-gradient(180deg, rgba(231,240,251,.95), rgba(221,234,248,.84));border:1px solid rgba(109,159,219,.22);color:#456a8d;font-size:13px;margin-bottom:12px;box-shadow:var(--shadow-soft)}
        .alert{padding:10px 12px;border:1px solid rgba(207,146,155,.28);background:linear-gradient(180deg, rgba(255,244,246,.96), rgba(246,226,230,.88));color:#8d5963;border-radius:12px;font-size:13px;box-shadow:var(--shadow-soft)}
        .toolbar{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
        .sticky-actions{position:sticky;top:0;z-index:5;background:linear-gradient(180deg,#ffffff 80%,rgba(255,255,255,.85) 100%);padding:8px;border:1px solid rgba(255,255,255,.82);border-radius:10px;box-shadow:var(--shadow-soft)}
        .table-wrap{overflow:auto;border:1px solid rgba(255,255,255,.82);border-radius:10px;box-shadow:var(--shadow-soft);background:rgba(255,255,255,.62)}
        .table-wrap table{min-width:760px}
        .stack{display:flex;flex-direction:column;gap:10px}
        .form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:10px}
        .field{display:flex;flex-direction:column;gap:5px}
        .label{font-size:12px;color:#516884;font-weight:600}
        input,select,textarea{width:100%;padding:10px 11px;border:1px solid var(--panel-strong);border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,.95), rgba(239,245,252,.82));color:var(--text);font-size:13px;outline:none;backdrop-filter:blur(10px);box-shadow:var(--shadow-inset)}
        input::placeholder,textarea::placeholder{color:#91a7c1}
        input:focus,select:focus,textarea:focus{border-color:rgba(109,159,219,.52);box-shadow:0 0 0 3px rgba(109,159,219,.12), var(--shadow-inset)}
        pre{margin:0;padding:12px;border:1px solid rgba(255,255,255,.82);border-radius:14px;background:rgba(247,250,255,.92);color:#3f5e7f;font-size:12px;max-height:320px;overflow:auto;box-shadow:var(--shadow-soft)}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 12px;border:1px solid rgba(255,255,255,.84);border-radius:12px;background:linear-gradient(180deg, rgba(255,255,255,.88), rgba(236,243,251,.8));color:var(--text);text-decoration:none;cursor:pointer;font-size:13px;font-weight:700;box-shadow:var(--shadow-soft)}
        .btn:hover{background:linear-gradient(180deg, rgba(255,255,255,.95), rgba(228,238,248,.84));border-color:rgba(109,159,219,.26)}
        .btn-primary{background:linear-gradient(90deg, rgba(231,240,251,.98), rgba(204,222,244,.92));border-color:rgba(109,159,219,.30);color:#315579}.btn-primary:hover{background:linear-gradient(90deg, rgba(236,244,252,.98), rgba(214,229,246,.92))}
        .btn-success{background:linear-gradient(90deg, rgba(235,248,243,.98), rgba(219,238,229,.92));border-color:rgba(115,171,153,.30);color:#417061}
        .btn-warn{background:linear-gradient(90deg, rgba(255,248,238,.98), rgba(247,231,205,.92));border-color:rgba(212,176,122,.30);color:#8a6730}
        .btn-danger{background:linear-gradient(90deg, rgba(255,244,246,.98), rgba(246,226,230,.92));border-color:rgba(207,146,155,.30);color:#8d5963}
        table{width:100%;border-collapse:separate;border-spacing:0;font-size:13px}
        th,td{padding:10px 11px;text-align:left;border-bottom:1px solid rgba(191,208,230,.38)}
        th{font-size:12px;color:#627894;background:rgba(255,255,255,.46);font-weight:700}
        tr:hover td{background:rgba(255,255,255,.24)}
        .empty{padding:20px;border:1px dashed rgba(170,190,216,.52);border-radius:14px;background:rgba(250,252,255,.8);color:var(--muted);font-size:13px;text-align:center;box-shadow:var(--shadow-soft)}
        .split{display:flex;gap:10px;flex-wrap:wrap}
        @media (max-width:1200px){.sidebar{display:none}.col-3,.col-4,.col-5,.col-6,.col-7,.col-8,.col-12{grid-column:span 12}.container{padding:16px}.sticky-actions{position:static;padding:6px}}

        /* Enterprise Top Navigation Shell */
        body{
            background:#f6f8fb !important;
            font-family:"Inter","Manrope",Segoe UI,Arial,sans-serif !important;
        }
        .app{
            display:block !important;
            min-height:100vh;
        }
        .sidebar{
            display:none !important;
        }
        .main{
            display:block !important;
            min-height:100vh;
            width:100%;
        }
        .top{
            display:none !important;
        }
        .container{
            padding:22px 28px !important;
            max-width:1480px;
            margin:0 auto;
        }
        .cb-shell{
            position:sticky;
            top:0;
            z-index:100;
            background:rgba(246,248,251,.92);
            backdrop-filter:blur(18px);
            border-bottom:1px solid rgba(203,213,225,.82);
        }
        .cb-topline{
            max-width:1480px;
            margin:0 auto;
            padding:10px 28px 0;
        }
        .cb-promo{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:22px;
            min-height:42px;
            padding:8px 18px;
            border-radius:999px;
            color:#dbeafe;
            background:linear-gradient(90deg,#111827,#0f3b34,#052e1a);
            box-shadow:0 12px 28px rgba(15,23,42,.10);
            font-size:13px;
            font-weight:650;
        }
        .cb-promo b{
            color:#bef264;
            font-family:"Manrope","Inter",sans-serif;
            font-size:18px;
            font-style:italic;
            letter-spacing:-.04em;
        }
        .cb-promo a{
            color:#bef264;
            text-decoration:none;
            font-weight:800;
        }
        .cb-nav{
            max-width:1480px;
            margin:0 auto;
            min-height:74px;
            padding:0 28px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:18px;
        }
        .cb-brand{
            display:flex;
            align-items:center;
            gap:12px;
            text-decoration:none;
            color:#0f172a;
            min-width:230px;
        }
        .cb-brand-mark{
            width:34px;
            height:34px;
            border-radius:10px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            color:#ffffff;
            background:linear-gradient(135deg,#fb3b22,#ff7a1a);
            font-size:15px;
            font-weight:900;
            letter-spacing:-.04em;
        }
        .cb-brand-text{
            display:flex;
            flex-direction:column;
            line-height:1.05;
        }
        .cb-brand-title{
            font-size:18px;
            font-weight:900;
            letter-spacing:-.035em;
        }
        .cb-brand-sub{
            margin-top:3px;
            color:#64748b;
            font-size:11px;
            font-weight:700;
            letter-spacing:.08em;
            text-transform:uppercase;
        }
        .cb-menu{
            display:flex;
            align-items:center;
            justify-content:center;
            gap:2px;
            flex:1;
        }
        .cb-menu-item{
            position:relative;
        }
        .cb-menu-btn{
            min-height:38px;
            padding:8px 13px;
            border:0;
            background:transparent;
            color:#0f172a;
            font-size:14px;
            font-weight:750;
            cursor:pointer;
            border-radius:999px;
            font-family:inherit;
        }
        .cb-menu-btn:hover{
            background:#eef4fb;
        }
        .cb-menu-btn:after{
            content:"";
            display:inline-block;
            width:6px;
            height:6px;
            margin-left:7px;
            border-right:1.5px solid currentColor;
            border-bottom:1.5px solid currentColor;
            transform:rotate(45deg) translateY(-2px);
        }
        .cb-dropdown{
            position:absolute;
            top:calc(100% + 10px);
            left:50%;
            transform:translateX(-50%);
            width:720px;
            display:none;
            grid-template-columns:repeat(3,minmax(0,1fr));
            gap:20px;
            padding:22px;
            background:rgba(255,255,255,.96);
            border:1px solid #dce5f2;
            border-radius:20px;
            box-shadow:0 24px 60px rgba(15,23,42,.16);
            backdrop-filter:blur(16px);
        }
        .cb-menu-item:hover .cb-dropdown{
            display:grid;
        }
        .cb-drop-col h4{
            margin:0 0 12px;
            color:#94a3b8;
            font-size:11px;
            font-weight:900;
            letter-spacing:.13em;
            text-transform:uppercase;
        }
        .cb-drop-col a{
            display:block;
            padding:8px 0;
            color:#334155;
            text-decoration:none;
            font-size:14px;
            font-weight:650;
        }
        .cb-drop-col a:hover{
            color:#0f172a;
        }
        .cb-actions{
            min-width:280px;
            display:flex;
            align-items:center;
            justify-content:flex-end;
            gap:10px;
        }
        .cb-action-primary{
            min-height:38px;
            padding:9px 18px;
            border-radius:999px;
            border:1px solid #0f172a;
            color:#0f172a;
            background:#ffffff;
            text-decoration:none;
            font-size:13px;
            font-weight:850;
        }
        .cb-action-hot{
            min-height:38px;
            padding:9px 18px;
            border-radius:999px;
            border:0;
            color:#ffffff;
            background:linear-gradient(135deg,#fb3b22,#ff6b2c);
            text-decoration:none;
            font-size:13px;
            font-weight:850;
        }
        .cb-user-chip{
            color:#64748b;
            font-size:12px;
            font-weight:700;
            white-space:nowrap;
        }
        .page-head{
            background:#ffffff !important;
            box-shadow:0 10px 24px rgba(15,23,42,.045) !important;
            border:1px solid #dce5f2 !important;
            border-radius:16px !important;
        }
        .card{
            box-shadow:0 10px 24px rgba(15,23,42,.045) !important;
            border-color:#dce5f2 !important;
        }
        @media(max-width:1120px){
            .cb-promo{display:none}
            .cb-nav{
                min-height:auto;
                padding:14px 18px;
                align-items:flex-start;
                flex-direction:column;
            }
            .cb-menu{
                justify-content:flex-start;
                flex-wrap:wrap;
                width:100%;
            }
            .cb-dropdown{
                left:0;
                transform:none;
                width:min(92vw,720px);
            }
            .cb-actions{
                justify-content:flex-start;
                min-width:0;
                flex-wrap:wrap;
            }
            .container{
                padding:16px !important;
            }
        }


        /* Topbar dropdown stability + compact colorful KPI cards */
        .cb-menu-item{
            padding:14px 0;
            margin:-14px 0;
        }

        .cb-dropdown{
            top:100% !important;
            margin-top:0 !important;
        }

        .cb-menu-item:hover .cb-dropdown,
        .cb-menu-item:focus-within .cb-dropdown,
        .cb-menu-item.is-open .cb-dropdown{
            display:grid !important;
        }

        .cb-dropdown:before{
            content:"";
            position:absolute;
            left:0;
            right:0;
            top:-14px;
            height:14px;
        }

        /* Compact KPI cards across pages */
        .grid > .col-3.card:has(.kpi),
        .grid > .col-4.card:has(.kpi),
        .grid > .col-6.card:has(.kpi){
            position:relative;
            overflow:hidden;
            min-height:96px !important;
            padding:16px 18px !important;
            border:1px solid #dbe5f3 !important;
            border-radius:16px !important;
            background:#ffffff !important;
            box-shadow:0 8px 22px rgba(15,23,42,.055) !important;
        }

        .grid > .col-3.card:has(.kpi):before,
        .grid > .col-4.card:has(.kpi):before,
        .grid > .col-6.card:has(.kpi):before{
            content:"";
            position:absolute;
            left:0;
            top:0;
            bottom:0;
            width:4px;
            background:#2563eb;
        }

        .grid > .col-3.card:has(.kpi):nth-child(2):before,
        .grid > .col-4.card:has(.kpi):nth-child(2):before,
        .grid > .col-6.card:has(.kpi):nth-child(2):before{
            background:#16a34a;
        }

        .grid > .col-3.card:has(.kpi):nth-child(3):before,
        .grid > .col-4.card:has(.kpi):nth-child(3):before,
        .grid > .col-6.card:has(.kpi):nth-child(3):before{
            background:#dc2626;
        }

        .grid > .col-3.card:has(.kpi):nth-child(4):before,
        .grid > .col-4.card:has(.kpi):nth-child(4):before,
        .grid > .col-6.card:has(.kpi):nth-child(4):before{
            background:#d97706;
        }

        .grid > .col-3.card:has(.kpi):after,
        .grid > .col-4.card:has(.kpi):after,
        .grid > .col-6.card:has(.kpi):after{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(135deg,rgba(37,99,235,.055),transparent 45%);
            pointer-events:none;
        }

        .grid > .col-3.card:has(.kpi):nth-child(2):after,
        .grid > .col-4.card:has(.kpi):nth-child(2):after,
        .grid > .col-6.card:has(.kpi):nth-child(2):after{
            background:linear-gradient(135deg,rgba(22,163,74,.06),transparent 45%);
        }

        .grid > .col-3.card:has(.kpi):nth-child(3):after,
        .grid > .col-4.card:has(.kpi):nth-child(3):after,
        .grid > .col-6.card:has(.kpi):nth-child(3):after{
            background:linear-gradient(135deg,rgba(220,38,38,.055),transparent 45%);
        }

        .grid > .col-3.card:has(.kpi):nth-child(4):after,
        .grid > .col-4.card:has(.kpi):nth-child(4):after,
        .grid > .col-6.card:has(.kpi):nth-child(4):after{
            background:linear-gradient(135deg,rgba(217,119,6,.07),transparent 45%);
        }

        .grid > .col-3.card:has(.kpi) .muted,
        .grid > .col-4.card:has(.kpi) .muted,
        .grid > .col-6.card:has(.kpi) .muted,
        .grid > .col-3.card:has(.kpi) .kpi,
        .grid > .col-4.card:has(.kpi) .kpi,
        .grid > .col-6.card:has(.kpi) .kpi,
        .grid > .col-3.card:has(.kpi) .badge,
        .grid > .col-4.card:has(.kpi) .badge,
        .grid > .col-6.card:has(.kpi) .badge{
            position:relative;
            z-index:1;
        }

        .grid > .col-3.card:has(.kpi) .kpi,
        .grid > .col-4.card:has(.kpi) .kpi,
        .grid > .col-6.card:has(.kpi) .kpi{
            font-size:26px !important;
            line-height:1.05 !important;
            margin:7px 0 6px !important;
            letter-spacing:-.045em;
        }


        /* Final compact colorful KPI cards */
        .people-kpi-grid{
            display:grid !important;
            grid-template-columns:repeat(4,minmax(0,1fr)) !important;
            gap:12px !important;
            margin-bottom:14px !important;
        }

        .people-kpi-grid > .people-kpi-card,
        .grid > .card:has(.kpi){
            position:relative !important;
            overflow:hidden !important;
            min-height:88px !important;
            padding:14px 16px !important;
            border:1px solid #dbe5f3 !important;
            border-radius:14px !important;
            background:#ffffff !important;
            box-shadow:0 6px 16px rgba(15,23,42,.045) !important;
            text-align:left !important;
        }

        .people-kpi-grid > .people-kpi-card{
            grid-column:auto !important;
            width:auto !important;
            max-width:none !important;
        }

        .people-kpi-grid > .people-kpi-card:before,
        .grid > .card:has(.kpi):before{
            content:"";
            position:absolute;
            left:0;
            top:0;
            bottom:0;
            width:4px;
            background:#2563eb;
        }

        .people-kpi-grid > .people-kpi-card:nth-child(2):before,
        .grid > .card:has(.kpi):nth-child(2):before{background:#16a34a}

        .people-kpi-grid > .people-kpi-card:nth-child(3):before,
        .grid > .card:has(.kpi):nth-child(3):before{background:#dc2626}

        .people-kpi-grid > .people-kpi-card:nth-child(4):before,
        .grid > .card:has(.kpi):nth-child(4):before{background:#d97706}

        .people-kpi-grid > .people-kpi-card:after,
        .grid > .card:has(.kpi):after{
            content:"";
            position:absolute;
            inset:0;
            background:linear-gradient(135deg,rgba(37,99,235,.055),transparent 46%);
            pointer-events:none;
        }

        .people-kpi-grid > .people-kpi-card:nth-child(2):after,
        .grid > .card:has(.kpi):nth-child(2):after{background:linear-gradient(135deg,rgba(22,163,74,.06),transparent 46%)}

        .people-kpi-grid > .people-kpi-card:nth-child(3):after,
        .grid > .card:has(.kpi):nth-child(3):after{background:linear-gradient(135deg,rgba(220,38,38,.055),transparent 46%)}

        .people-kpi-grid > .people-kpi-card:nth-child(4):after,
        .grid > .card:has(.kpi):nth-child(4):after{background:linear-gradient(135deg,rgba(217,119,6,.07),transparent 46%)}

        .people-kpi-card .muted,
        .people-kpi-card .kpi,
        .people-kpi-card .people-help,
        .grid > .card:has(.kpi) .muted,
        .grid > .card:has(.kpi) .kpi,
        .grid > .card:has(.kpi) .badge{
            position:relative;
            z-index:1;
        }

        .people-kpi-card .kpi,
        .grid > .card:has(.kpi) .kpi{
            font-size:25px !important;
            line-height:1.05 !important;
            margin:6px 0 4px !important;
            letter-spacing:-.04em !important;
        }

        .people-kpi-card .muted,
        .grid > .card:has(.kpi) .muted{
            font-size:12px !important;
            font-weight:750 !important;
            color:#64748b !important;
        }

        .people-help{
            font-size:12px !important;
            color:#64748b !important;
            margin-top:4px !important;
        }

        .people-kpi-card:hover,
        .grid > .card:has(.kpi):hover{
            transform:none !important;
            box-shadow:0 8px 20px rgba(37,99,235,.07) !important;
            border-color:#bfdbfe !important;
        }

        @media(max-width:1200px){
            .people-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important}
        }

        @media(max-width:720px){
            .people-kpi-grid{grid-template-columns:1fr !important}
        }

    
/* Phase 1B Billing UI context badge - labels only */
.phase1b-billing-context{
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-left:12px;
    padding:7px 10px;
    border:1px solid rgba(15,23,42,.12);
    border-radius:999px;
    background:rgba(255,255,255,.78);
    color:#334155;
    font-size:12px;
    font-weight:700;
    box-shadow:0 6px 18px rgba(15,23,42,.06);
}
.phase1b-badge{
    display:inline-flex;
    align-items:center;
    padding:3px 8px;
    border-radius:999px;
    background:#eef2ff;
    color:#3730a3;
    font-size:11px;
    font-weight:800;
    letter-spacing:.02em;
}

</style>
    <script defer src="/js/crud-grids.js?v=20260520-1611"></script>
    <link rel="stylesheet" href="{{ asset('css/enterprise-shell.css') }}?v={{ filemtime(public_path('css/enterprise-shell.css')) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>
<body>
<div class="app">

    @if(session('user_id'))
    <header class="cb-shell">
        <div class="cb-topline">
            <div class="cb-promo">
                <b>Colony Billing</b>
                <span>Enterprise billing, residency and utility operations workspace.</span>
                <a href="/control-room">Billing Center →</a>
            </div>
        </div>
        <div class="cb-nav">
            <a class="cb-brand" href="/dashboard">
                <span class="cb-brand-mark">CB</span>
                <span class="cb-brand-text">
                    <span class="cb-brand-title">Colony Billing</span>
                    <span class="cb-brand-sub">Operations Console</span>
                </span>
            </a>

            <nav class="cb-menu" aria-label="Main navigation">
                <div class="cb-menu-item">
                    <button class="cb-menu-btn" type="button">Core</button>
                    <div class="cb-dropdown">
                        <div class="cb-drop-col">
                            <h4>Workspace</h4>
                            <a href="/dashboard">Dashboard</a>
                            <a href="/month-lifecycle">Billing Month Control</a>
                            <a href="/imports-validation">Imports & Validation</a>
                        </div>
                        <div class="cb-drop-col">
                            <h4>Billing</h4>
                            <a href="/control-room">Billing Center</a>
                            <a href="/reporting">Reporting Center</a>
                            <a href="/rates">Rates</a>
                        </div>
                        <div class="cb-drop-col">
                            <h4>Controls</h4>
                            <a href="/active-days-monthly">Monthly Attendance</a>
                            <a href="/meters-readings">Meter Readings</a>
                            <a href="/unit-directory">Unit Directory</a>
                        </div>
                    </div>
                </div>

                <div class="cb-menu-item">
                    <button class="cb-menu-btn" type="button">Operations</button>
                    <div class="cb-dropdown">
                        <div class="cb-drop-col">
                            <h4>People</h4>
                            <a href="/people-residency">People & Housing</a>
                            <a href="/housing-occupancy">Housing & Occupancy</a>
                            <a href="/transport">School Van Kids Management</a>
                        </div>
                        <div class="cb-drop-col">
                            <h4>Utilities</h4>
                            <a href="/meters-readings">Meter Readings</a>
                            <a href="/water-tools">Water Tools</a>
                            {{-- Phase 1C: Electric V1 hidden from staff nav; admin diagnostics kept by route. --}}
                        </div>
                        <div class="cb-drop-col">
                            <h4>Data</h4>
                            <a href="/unit-directory">Unit Directory</a>
                            <a href="/active-days-monthly">Monthly Attendance</a>
                            <a href="/imports-validation">Import Validation</a>
                        </div>
                        <div class="cb-drop-col">
                            <h4>Maintenance Operations</h4>
                            <a href="/facilities-management">Facilities Workspace</a>
                            <a href="/facilities-management/registry">Facility Registry</a>
                        </div>
                    </div>
                </div>

                <div class="cb-menu-item">
                    <button class="cb-menu-btn" type="button">Reports</button>
                    <div class="cb-dropdown">
                        <div class="cb-drop-col">
                            <h4>Billing Reports</h4>
                            <a href="/reporting">Reporting Center</a>
                            <a href="/reports/employee-statement">Employee Statement</a>
                            <a href="/finalized-months">Finalized Months</a>
                        </div>
                        <div class="cb-drop-col">
                            <h4>Utilities</h4>
                            <a href="/elec-summary">Electric Summary</a>
                            <a href="/water-tools">Water Tools</a>
                        </div>
                        <div class="cb-drop-col">
                            <h4>Audit</h4>
                            <a href="/control-room">Billing Center</a>
                            <a href="/imports-validation">Validation Tokens</a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="cb-actions">
                <a class="cb-action-hot" href="/people-residency">People</a>
                <span class="cb-user-chip">User #{{ session('user_id', 'N/A') }} · {{ session('role', 'N/A') }}</span>
            </div>
        </div>
    </header>
    @endif

    @if(session('user_id'))
    <aside class="sidebar">
        <div class="brand">Colony Billing</div>
        <div class="sub">Premium Light Workspace</div>
        <nav class="nav">
            <div class="nav-section">
                <div class="nav-head">Core</div>
                <a class="{{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard"><span class="nav-ico">D</span>Dashboard</a>
                <a class="{{ request()->is('month-lifecycle') ? 'active' : '' }}" href="/month-lifecycle"><span class="nav-ico">M</span>Billing Month Control</a>
                <a class="{{ request()->is('imports-validation') ? 'active' : '' }}" href="/imports-validation"><span class="nav-ico">I</span>Imports & Validation</a>
                <a class="{{ request()->is('control-room') || request()->is('control-room/*') ? 'active' : '' }}" href="/control-room"><span class="nav-ico">B</span>Billing Center</a>
                <a class="{{ request()->is('reporting') ? 'active' : '' }}" href="/reporting"><span class="nav-ico">R</span>Reporting Center</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">Operations</div>
                <a class="{{ request()->is('people-residency') ? 'active' : '' }}" href="/people-residency"><span class="nav-ico">P</span>People & Housing</a>
                <a class="{{ request()->is('active-days-monthly') || request()->is('ui/monthly-active-days') ? 'active' : '' }}" href="/active-days-monthly"><span class="nav-ico">AD</span>Monthly Attendance</a>
                <a class="{{ request()->is('transport') ? 'active' : '' }}" href="/transport"><span class="nav-ico">S</span>School Van Kids Management</a>
                <a class="{{ request()->is('meters-readings') ? 'active' : '' }}" href="/meters-readings"><span class="nav-ico">MR</span>Meter Readings</a>
                <a class="{{ request()->is('unit-directory') ? 'active' : '' }}" href="/unit-directory"><span class="nav-ico">U</span>Unit Directory</a>
                <a class="{{ request()->is('ui/residency-master') ? 'active' : '' }}" href="/ui/residency-master"><span class="nav-ico">RM</span>Residency Master</a>
                <a class="{{ request()->is('ui/department-master') ? 'active' : '' }}" href="/ui/department-master"><span class="nav-ico">DM</span>Department Master</a>
                <a class="{{ request()->is('housing-rooms') || request()->is('housing-occupancy') ? 'active' : '' }}" href="/housing-occupancy"><span class="nav-ico">H</span>Housing & Occupancy</a>
                {{-- Phase 1D: Electric V1 hidden from staff navigation; route kept for admin diagnostics. --}}
                <a class="{{ request()->is('rates') ? 'active' : '' }}" href="/rates"><span class="nav-ico">$</span>Rates</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">Maintenance Operations</div>
                <a class="{{ request()->is('facilities-management') || request()->is('facilities-management/*') ? 'active' : '' }}" href="/facilities-management"><span class="nav-ico">FM</span>Facilities Workspace</a>
            </div>
            <div class="nav-section">
                <div class="nav-head">Profile</div>
                <a href="/profile"><span class="nav-ico">MP</span>My Profile</a>
                @if(in_array(session('role'), ['SUPER_ADMIN']))
                    <a class="{{ request()->is('ui/admin/users') ? 'active' : '' }}" href="/ui/admin/users"><span class="nav-ico">UM</span>User Management</a>
                @endif
                <a href="/logout"><span class="nav-ico">X</span>Logout</a>
            </div>
        </nav>
    </aside>
    @endif

    <main class="main">
        <header class="top">
            <div class="top-title">@yield('page_title', 'Colony Billing')</div>
@php
    $phase1Month = request('month_cycle', request('month', session('billing_month', 'Select Month')));
@endphp
<div class="phase1b-billing-context" title="Phase 1 UI label only">
    <span>Billing Month: {{ $phase1Month }}</span>
    <span class="phase1b-badge">Draft View</span>
</div>

            <div class="user">User #{{ session('user_id', 'N/A') }} · Role: {{ session('role', 'N/A') }}</div>
        </header>
        <section class="container">
            <div class="page-head">
                <div>
                    <div class="crumb">Billing / Workspace</div>
                    <h1 class="page-title">@yield('page_title', 'Colony Billing')</h1>
                    @hasSection('page_subtitle')<p class="page-sub">@yield('page_subtitle')</p>@endif
                </div>
                @hasSection('page_actions')<div class="toolbar">@yield('page_actions')</div>@endif
            </div>
            @if(session('status'))<div class="banner">{{ session('status') }}</div>@endif
            @yield('content')
        </section>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.cb-menu-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const item = btn.closest('.cb-menu-item');
      document.querySelectorAll('.cb-menu-item.is-open').forEach(function (openItem) {
        if (openItem !== item) openItem.classList.remove('is-open');
      });
      item.classList.toggle('is-open');
    });
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.cb-menu-item')) {
      document.querySelectorAll('.cb-menu-item.is-open').forEach(function (item) {
        item.classList.remove('is-open');
      });
    }
  });
});
</script>

</body>
</html>
