<html>
<header>
<title>Aset Sedang Dalam Pengiriman</title>
</header>
<body>
<div>
<h2>Permintaan Pemindahan Aset Telah Dikirim!</h2>
<p>Permintaan untuk memindahkan aset dengan nomor dokumen <?php echo $document['docnum']; ?>, telah dikirim oleh <?php echo $document['docsender']; ?></p>
<p>Detil permintaan perpindahan aset:</p>
<table width="100%">
<tbody>
<tr><td>No. Dokumen Permintaan</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Tgl. Dokumen</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Pemohon</td><td>:</td><td><?php echo $document['docapp']; ?></td></tr>
<tr><td>Status</td><td>:</td><td><b>DIKIRIM</b></td></tr>
<tr><td>Pengirim</td><td>:</td><td><?php echo $document['docsender']; ?></td></tr>
<tr><td>Waktu</td><td>:</td><td><?php echo $document['docsenttime']; ?></td></tr>
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
<h2>Asset Transfer Request Being Sent!</h2>
<p>The request to transfer assets with document number <?php echo $document['docnum']; ?>, has been sent by <?php echo $document['docsender']; ?></p>
<p>Detailed asset transfer request:</p>
<table width="100%">
<tbody>
<tr><td>Request No.</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Document Date</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Applicant</td><td>:</td><td><?php echo $document['docapp']; ?></td></tr>
<tr><td>Status</td><td>:</td><td><b>SENT</b></td></tr>
<tr><td>Sender</td><td>:</td><td><?php echo $document['docsender']; ?></td></tr>
<tr><td>Time</td><td>:</td><td><?php echo $document['docsenttime']; ?></td></tr>
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
