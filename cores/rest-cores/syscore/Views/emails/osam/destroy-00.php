<html>
<header>
<title>Permintaan Baru Pemusnahan Aset/title>
</header>
<body>
<div>
<h2>Permintaan Pemusnahan Aset Baru Telah Dibuat!</h2>
<p>Dokumen pemusnahan aset no. <?php echo $document['docnum']; ?> telah dibuat oleh <?php echo $document['docreq']; ?></p>
<p>Detil permintaan pemusnahan aset:</p>
<table width="100%">
<tbody>
<tr><td>No. Dokumen</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Tgl. Dokumen</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Lokasi</td><td>:</td><td><?php echo $document['docloc']; ?></td></tr>
<tr><td>Pemohon</td><td>:</td><td><?php echo $document['docreq']; ?></td></tr>
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
<h2>New Request for Asset Disposal Created!</h2>
<p>Document disposal number <?php echo $document['docnum']; ?>, has been created by <?php echo $document['docreq']; ?></p>
<p>Detailed asset disposal request:</p>
<table width="100%">
<tbody>
<tr><td>Document No.</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Document Date/td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Location</td><td>:</td><td><?php echo $document['docloc']; ?></td></tr>
<tr><td>Suppliant</td><td>:</td><td><?php echo $document['docreq']; ?></td></tr>
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
