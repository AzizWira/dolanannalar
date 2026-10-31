document.addEventListener('DOMContentLoaded',()=>{
 const toggle=document.querySelector('.menu-toggle'),nav=document.querySelector('#navigation');
 if(toggle&&nav){const close=()=>{toggle.setAttribute('aria-expanded','false');nav.classList.remove('open')};toggle.addEventListener('click',()=>{const open=toggle.getAttribute('aria-expanded')!=='true';toggle.setAttribute('aria-expanded',String(open));nav.classList.toggle('open',open)});document.addEventListener('keydown',e=>{if(e.key==='Escape'&&nav.classList.contains('open')){close();toggle.focus()}});document.addEventListener('click',e=>{if(!nav.contains(e.target)&&!toggle.contains(e.target))close()});}
 document.querySelectorAll('[data-password]').forEach(button=>button.addEventListener('click',()=>{const input=document.getElementById(button.dataset.password);const show=input.type==='password';input.type=show?'text':'password';button.textContent=show?'Sembunyi':'Lihat';button.setAttribute('aria-label',show?'Sembunyikan password':'Tampilkan password')}));
 document.querySelectorAll('[data-add]').forEach(button=>button.addEventListener('click',()=>{
   const target=document.getElementById(button.dataset.target), service=button.dataset.add==='service';
   const rows=target.children;if(rows.length>=20){button.textContent='Maksimal 20 item';return;}
   const index=rows.length;
   const row=document.createElement(service?'fieldset':'div');
   if(service)row.innerHTML=`<legend>Layanan ${index+1}</legend><div class="field-grid"><label>Judul<input name="services[${index}][title]" required maxlength="100"></label><label>Label jenjang<input name="services[${index}][level]" required maxlength="80"></label></div><label>Deskripsi<textarea name="services[${index}][text]" rows="3" required maxlength="1000"></textarea></label><label>Ilustrasi<select name="services[${index}][image]"><option value="jelajah-cerita">Buku cerita</option><option value="bermain-angka">Permainan angka</option><option value="tumbuh-bersama">Proses bertumbuh</option><option value="belajar-di-rumah">Belajar privat</option><option value="ruang-orang-tua">Orang tua</option></select></label>`;
   else{row.className='field-grid';row.innerHTML=`<label>Jenjang<input name="prices[${index}][label]" required maxlength="80"></label><label>Harga (rupiah)<input type="number" min="0" name="prices[${index}][price]" required></label>`;}
   target.append(row);row.querySelector('input')?.focus();
 }));
 const logoutDialog=document.getElementById('logout-dialog');
 let pendingLogoutForm=null;
 document.querySelectorAll('[data-logout-form]').forEach(form=>form.addEventListener('submit',event=>{
   if(form.dataset.logoutConfirmed==='1')return;
   if(!logoutDialog||typeof logoutDialog.showModal!=='function'){
     if(!window.confirm('Yakin ingin keluar dari akun?'))event.preventDefault();
     return;
   }
   event.preventDefault();
   pendingLogoutForm=form;
   logoutDialog.showModal();
 }));
 if(logoutDialog){
   logoutDialog.querySelector('[data-logout-cancel]').addEventListener('click',()=>logoutDialog.close());
   logoutDialog.querySelector('[data-logout-confirm]').addEventListener('click',()=>{
     const form=pendingLogoutForm;
     if(!form)return;
     form.dataset.logoutConfirmed='1';
     logoutDialog.close();
     form.requestSubmit();
   });
   logoutDialog.addEventListener('click',event=>{
     const rect=logoutDialog.getBoundingClientRect();
     if(event.target===logoutDialog&&(event.clientX<rect.left||event.clientX>rect.right||event.clientY<rect.top||event.clientY>rect.bottom))logoutDialog.close();
   });
   logoutDialog.addEventListener('close',()=>{pendingLogoutForm=null});
 }
 document.querySelectorAll('form').forEach(form=>form.addEventListener('submit',event=>{if(event.defaultPrevented||!form.checkValidity())return;if(form.dataset.submitting==='1'){event.preventDefault();return}form.dataset.submitting='1';setTimeout(()=>{form.dataset.submitting='0'},8000)}));
});
