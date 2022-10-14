<html>
<header>
<title>Permintaan Ditolak</title>
</header>
<body>
<div>
<h2>Anda Telah Menolak Permohonan Perpindahan Aset!</h2>
<p>Permohonan perpindahan aset dengan nomor <b><?php echo $document['docnum']; ?></b> telah anda tolak! pada <?php echo $document['docactdate']; ?></p>
<p>Detil permintaan perpindahan aset:</p>
<table width="100%">
<tbody>
<tr><td>No. Dokumen Permintaan</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Tgl. Dokumen</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Pemohon</td><td>:</td><td><?php echo $document['docapp']; ?></td></tr>
<tr><td>Status</td><td>:</td><td><b>DITOLAK</b></td></tr>
</tbody>
</table>
<br />
<p>Terima Kasih</p>
<br />
<p>Sistem Informasi Pengelolaan Aset</p>
<br />
<small>Ini adalah pesan otomatis yang dikirim ke email anda, jika ada tanggapan mohon untuk segera hubungi staff IT anda!</small>
</div>
<br />
<hr />
<br />
<div>
<h2>You Declined An Asset Transfer Request!</h2>
<p>You have declined asset transfer request <b><?php echo $document['docnum']; ?></b> on <?php echo $document['docactdate']; ?></p>
<p>Detailed asset transfer request:</p>
<table width="100%">
<tbody>
<tr><td>Request No.</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Document Date</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Applicant</td><td>:</td><td><?php echo $document['docapp']; ?></td></tr>
<tr><td>Status</td><td>:</td><td><b>DECLINED</b></td></tr>
</tbody>
</table>
<br />
<p>Thank You</p>
<br />
<p>Asset Management System Information</p>
<br />
<small>This is an automated message delivery to your email, if your have comments or questions please immediately contact your IT staff</small>
</div>
</body>
</html>
