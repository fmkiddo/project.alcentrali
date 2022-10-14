<html>
<header>
<title>Aset Sedang Telah Berhasil Diterima</title>
</header>
<body>
<div>
<h2>Permintaan Pemindahan Aset Telah Diterima di Tujuan!</h2>
<p>Dokumen pengiriman aset no. <?php echo $document['docnum']; ?> telah diterima oleh <?php echo $document['docrcv']; ?></p>
<p>Detil permintaan perpindahan aset:</p>
<table width="100%">
<tbody>
<tr><td>No. Dokumen</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Tgl. Dokumen</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Status</td><td>:</td><td><b>DITERIMA</b></td></tr>
<tr><td>Penerima</td><td>:</td><td><?php echo $document['docrcv']; ?></td></tr>
<tr><td>Waktu</td><td>:</td><td><?php echo $document['docrcvtime']; ?></td></tr>
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
<h2>Asset Transfer Has Been Received!</h2>
<p>Document asset transfer number <?php echo $document['docnum']; ?>, has been received by <?php echo $document['docrcv']; ?></p>
<p>Detailed asset transfer request:</p>
<table width="100%">
<tbody>
<tr><td>Request No.</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Document Date</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Status</td><td>:</td><td><b>RECEIVED</b></td></tr>
<tr><td>Sender</td><td>:</td><td><?php echo $document['docrcv']; ?></td></tr>
<tr><td>Time</td><td>:</td><td><?php echo $document['docrcvtime']; ?></td></tr>
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
