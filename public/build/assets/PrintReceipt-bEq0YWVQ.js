import{f as h,o as $,a as t}from"./helpers-B8cAmY3s.js";import"./app-Dx_R7mNc.js";const l=(a,n)=>{var r,m,c,v;const x=`
    <!DOCTYPE html>
    <html>
    <head>
        <title>Receipt - ${a.pos_number}</title>
        <style>
            @page {
                size: 80mm auto;
                margin: 0;
            }
            @media print {
                body { 
                    width: 80mm;
                    margin: 0;
                    padding: 0;
                }
            }
            body { 
                font-family: 'Courier New', monospace; 
                width: 80mm;
                margin: 0; 
                padding: 0;
                font-size: 12px;
                line-height: 1.3;
                color: #000;
            }
            .receipt { 
                width: 100%;
                text-align: center;
                padding: 5mm;
                margin: 0;
                box-sizing: border-box;
            }
            .header {
                margin-bottom: 8px;
            }
            .company-name {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 3px;
                letter-spacing: 0.5px;
            }
            .company-info {
                font-size: 11px;
                line-height: 1.4;
                margin-bottom: 5px;
            }
            .separator {
                border-top: 2px dashed #000;
                margin: 8px 0;
            }
            .receipt-info {
                text-align: left;
                margin-bottom: 6px;
            }
            .info-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
                font-size: 12px;
            }
            .items-section {
                text-align: left;
                margin-bottom: 6px;
            }
            .item {
                margin-bottom: 10px;
                border-bottom: 1px dotted #000;
                padding-bottom: 5px;
            }
            .item-name {
                font-weight: bold;
                font-size: 13px;
                margin-bottom: 3px;
            }
            .item-details {
                font-size: 11px;
            }
            .item-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 2px;
            }
            .totals {
                text-align: left;
                margin-bottom: 6px;
            }
            .total-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 3px;
                font-size: 12px;
            }
            .final-total {
                display: flex;
                justify-content: space-between;
                font-weight: bold;
                font-size: 16px;
                border-top: 2px solid #000;
                padding-top: 5px;
                margin-top: 5px;
            }
            .footer {
                text-align: center;
                margin-top: 10px;
                font-size: 11px;
            }
            .thank-you {
                font-weight: bold;
                margin-bottom: 3px;
            }
        </style>
    </head>
    <body>
        <div class="receipt">
            <div class="header">
                <div class="company-name">${(n==null?void 0:n.company_name)||"COMPANY NAME"}</div>
                <div class="company-info">
                    ${(n==null?void 0:n.company_address)||"Company Address"}<br>
                    ${(n==null?void 0:n.company_city)||"City"}, ${(n==null?void 0:n.company_state)||"State"}<br>
                    ${(n==null?void 0:n.company_country)||"Country"} - ${(n==null?void 0:n.company_zipcode)||"Zipcode"}
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="receipt-info">
                <div class="info-row">
                    <span>Receipt No:</span>
                    <span>${a.pos_number}</span>
                </div>
                <div class="info-row">
                    <span>Date:</span>
                    <span>${h(new Date,{companyAllSetting:n})}</span>
                </div>
                <div class="info-row">
                    <span>Time:</span>
                    <span>${$(new Date().toLocaleTimeString(),{companyAllSetting:n})}</span>
                </div>
                <div class="info-row">
                    <span>Customer:</span>
                    <span>${((r=a.customer)==null?void 0:r.name)||"Walk-in Customer"}</span>
                </div>
            </div>
            
            <div class="separator"></div>
            
            <div class="items-section">
                ${a.items.map(i=>{const y=i.price*i.quantity,o=i.item_discount_amount||0,f=y-o;let p=0;const u=i.taxes&&i.taxes.length>0?i.taxes.reduce((d,w)=>d+w.rate,0):0;return i.taxes&&i.taxes.length>0&&i.taxes.forEach(d=>{p+=f*d.rate/100}),`
                        <div class="item">
                            <div class="item-name">${i.name}</div>
                            <div class="item-details">
                                <div class="item-row">
                                    <span>Qty:</span>
                                    <span>${i.quantity}</span>
                                </div>
                                <div class="item-row">
                                    <span>Price:</span>
                                    <span>${t(i.price,{companyAllSetting:n})}</span>
                                </div>
                                <div class="item-row">
                                    <span>Subtotal:</span>
                                    <span>${t(y,{companyAllSetting:n})}</span>
                                </div>
                                ${o>0?`
                                <div class="item-row" style="color: #16a34a;">
                                    <span>Discount:</span>
                                    <span>-${t(o,{companyAllSetting:n})}</span>
                                </div>
                                `:""}
                                <div class="item-row">
                                    <span>Tax (${u}%):</span>
                                    <span>${t(p,{companyAllSetting:n})}</span>
                                </div>
                                <div class="item-row" style="font-weight: bold;">
                                    <span>Total:</span>
                                    <span>${t(f+p,{companyAllSetting:n})}</span>
                                </div>
                            </div>
                        </div>
                    `}).join("")}
            </div>
            
            <div class="totals">
                <div class="final-total">
                    <span>TOTAL:</span>
                    <span>${t(a.total,{companyAllSetting:n})}</span>
                </div>
                <div class="item-details" style="margin-top: 5px;">
                    <div class="item-row">
                        <span>Payment Method:</span>
                        <span style="text-transform: uppercase;">${(a.payment_method||"cash").replace("_"," ")}</span>
                    </div>
                    ${a.reference_number?`
                    <div class="item-row">
                        <span>Reference:</span>
                        <span>${a.reference_number}</span>
                    </div>`:""}
                    ${a.paid_amount!==void 0?`
                    <div class="item-row">
                        <span>Tender / Paid:</span>
                        <span>${t(a.paid_amount,{companyAllSetting:n})}</span>
                    </div>`:""}
                    ${a.change_amount!==void 0&&a.change_amount>0?`
                    <div class="item-row" style="font-weight: bold;">
                        <span>Change Due:</span>
                        <span>${t(a.change_amount,{companyAllSetting:n})}</span>
                    </div>`:""}
                </div>
            </div>
            
            <div class="footer">
                <div class="thank-you">*** THANK YOU ***</div>
                <div>Visit Again!</div>
            </div>
        </div>
    </body>
    </html>
    `,s=document.createElement("iframe");s.style.display="none",document.body.appendChild(s);const e=s.contentDocument||((m=s.contentWindow)==null?void 0:m.document);e&&(e.write(x),e.close(),(c=s.contentWindow)==null||c.focus(),(v=s.contentWindow)==null||v.print(),setTimeout(()=>{document.body.removeChild(s)},1e3))};export{l as printReceipt};
