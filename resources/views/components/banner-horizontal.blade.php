 <style>
     .vigilance-sayhi-banner {
         display: flex;
         align-items: center;
         justify-content: space-between;
         gap: 16px;
         padding: 12px 16px;
         border-radius: 12px;
         background: linear-gradient(90deg, #fde9ee 0%, #eaf6ff 38%, #dff9f1 100%);
         border: 1px solid rgba(0, 0, 0, 0.06);
         box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
         overflow: hidden;
         position: relative;
     }
 
     .vigilance-sayhi-left {
         display: flex;
         align-items: center;
         gap: 14px;
         min-width: 0;
     }

     .vigilance-sayhi-middle {
         flex: 1.4 1 auto;
         display: flex;
         flex-direction: column;
         align-items: center;
         justify-content: center;
         gap: 4px;
         min-width: 0;
         padding: 0 4px;
     }
     .vigilance-sayhi-middle img {
         display: block;
         max-height: 58px;
         width: auto;
         max-width: 360px;
         object-fit: contain;
         filter: drop-shadow(0 10px 14px rgba(0,0,0,0.08));
     }
     .vigilance-sayhi-middle-caption {
         display: flex;
         align-items: center;
         justify-content: center;
         gap: 10px;
         flex-wrap: wrap;
         font-weight: 800;
         font-size: 12px;
         color: rgba(10, 27, 61, 0.70);
         text-align: center;
         line-height: 1.1;
         white-space: nowrap;
     }
     .vigilance-sayhi-middle-caption i {
         font-size: 13px;
         margin-right: 6px;
     }
     .vigilance-sayhi-middle-caption .item {
         display: inline-flex;
         align-items: center;
         background: rgba(255, 255, 255, 0.65);
         border: 1px solid rgba(0, 0, 0, 0.06);
         border-radius: 999px;
         padding: 5px 10px;
     }
 
     .vigilance-sayhi-hello {
         display: flex;
         flex-direction: column;
         line-height: 1.05;
         padding-right: 2px;
         min-width: 120px;
     }
     .vigilance-sayhi-hello .title {
         font-weight: 900;
         font-size: 22px;
         color: #0a1b3d;
     }
     .vigilance-sayhi-hello .sub {
         font-weight: 700;
         font-size: 12px;
         color: rgba(10, 27, 61, 0.70);
     }
 
     .vigilance-sayhi-brand {
         display: flex;
         align-items: center;
         gap: 10px;
         min-width: 0;
         flex-wrap: nowrap;
     }
     .vigilance-sayhi-brand .name {
         font-weight: 900;
         font-size: 24px;
         letter-spacing: 0.4px;
         color: #ff1b55;
         flex: 0 0 auto;
         white-space: nowrap;
     }
     .vigilance-sayhi-brand .pill {
         font-weight: 800;
         font-size: 12px;
         color: rgba(10, 27, 61, 0.78);
         background: rgba(255, 255, 255, 0.75);
         border: 2px solid rgba(24, 166, 163, 0.18);
         border-radius: 999px;
         padding: 7px 12px;
         white-space: normal;
         flex: 0 1 auto;
         min-width: 0;
         line-height: 1.15;
     }
 
     .vigilance-sayhi-right {
         display: flex;
         align-items: center;
         gap: 12px;
         flex-shrink: 0;
     }
     .vigilance-sayhi-promo {
         display: flex;
         align-items: center;
         gap: 10px;
         background: rgba(255, 255, 255, 0.82);
         border: 1px solid rgba(0, 0, 0, 0.08);
         border-radius: 10px;
         padding: 10px 12px;
         line-height: 1.05;
     }
     .vigilance-sayhi-promo .icon {
         display: inline-flex;
         align-items: center;
         justify-content: center;
         width: 30px;
         height: 30px;
         border-radius: 10px;
         background: rgba(255, 27, 85, 0.10);
         color: #ff1b55;
         border: 1px solid rgba(255, 27, 85, 0.18);
         flex: 0 0 auto;
     }
     .vigilance-sayhi-promo .icon i {
         font-size: 16px;
         line-height: 1;
     }
     .vigilance-sayhi-promo .label {
         font-weight: 800;
         font-size: 12px;
         color: rgba(10, 27, 61, 0.75);
         white-space: nowrap;
     }
     .vigilance-sayhi-promo .value {
         font-weight: 900;
         font-size: 22px;
         color: #ff1b55;
         white-space: nowrap;
     }
 
     .vigilance-sayhi-cta {
         display: inline-flex;
         align-items: center;
         gap: 8px;
         font-weight: 900;
         font-size: 13px;
         color: #ffffff;
         background: linear-gradient(90deg, #12a6ff 0%, #20d57a 100%);
         border: none;
         border-radius: 12px;
         padding: 11px 14px;
         text-decoration: none;
         white-space: nowrap;
         box-shadow: 0 10px 18px rgba(18, 166, 255, 0.22);
     }
     .vigilance-sayhi-cta .dot {
         display: inline-flex;
         width: 18px;
         height: 18px;
         border-radius: 6px;
         background: rgba(255, 255, 255, 0.22);
         align-items: center;
         justify-content: center;
         font-weight: 900;
         line-height: 1;
     }
 
     @media (max-width: 992px) {
         .vigilance-sayhi-hello {
             min-width: 0;
         }
         .vigilance-sayhi-brand .name {
             font-size: 22px;
         }
         .vigilance-sayhi-promo .value {
             font-size: 20px;
         }
     }
     @media (max-width: 768px) {
         .vigilance-sayhi-banner {
             flex-direction: column;
             align-items: stretch;
         }
         .vigilance-sayhi-middle {
             width: 100%;
             padding: 6px 0;
         }
         .vigilance-sayhi-right {
             justify-content: flex-end;
             width: 100%;
         }
     }
 </style>
 
 <div class="vigilance-sayhi-banner">
     <div class="vigilance-sayhi-left">
         <div class="vigilance-sayhi-hello">
             <div class="title">Say Hi!</div>
             <div class="sub">Chào các doanh nghiệp</div>
         </div>
 
         <div class="vigilance-sayhi-brand">
             <div class="name">VIGILANCE</div>
             <div class="pill">Giải pháp an toàn - vận hành thông minh</div>
         </div>
     </div>

     <div class="vigilance-sayhi-middle" aria-hidden="true">
         <img src="{{ asset('images/image.png') }}" alt="">
         <div class="vigilance-sayhi-middle-caption">
             <span class="item"><i class="bi bi-headset"></i>Tư vấn 24/7</span>
             <span class="item"><i class="bi bi-truck"></i>Giao hàng nhanh</span>
             <span class="item"><i class="bi bi-shield-check"></i>Bảo hành chính hãng</span>
         </div>
     </div>
 
     <div class="vigilance-sayhi-right">
         <div class="vigilance-sayhi-promo">
             <div class="icon" aria-hidden="true"><i class="bi bi-tag-fill"></i></div>
             <div class="label">Ưu đãi lên đến</div>
             <div class="value">5 Triệu</div>
         </div>
         <a class="vigilance-sayhi-cta" href="#">
             <span class="dot">›</span>
             <span>XEM CHI TIẾT</span>
         </a>
     </div>
 </div>
