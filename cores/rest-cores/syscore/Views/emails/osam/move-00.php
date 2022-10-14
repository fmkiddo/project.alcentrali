<html>
<header>
<title></title>
</header>
<body>
<div>
<h2>Ada Permintaan Perpindahan Aset Yang Baru!</h2>
<p>Permintaan perpindahan aset baru saja dibuat. Segera <a href="https://app.jodamoexchange.com/assets-manager/id/assets/user-login">klik disini</a> untuk segera memberikan tanggapan anda.</p>
<br />
<p>Detil permintaan perpindahan aset</p>
<table width="100%">
<tbody>
<tr><td>No. Dokumen Permintaan</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>No. Dokumen Pemindahan</td><td>:</td><td></td><?php echo $document['docnum_transfer']; ?></td></tr>
<tr><td>Tgl. Dokumen</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Lokasi Asal</td><td>:</td><td><?php echo $document['docfrom']; ?></td></tr>
<tr><td>Pemohon</td><td>:</td><td><?php echo $document['docapp']; ?></td></tr>
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
<h2>New Asset Transfer Requested!</h2>
<p>New asset transfer requests are made. Please kindly review this request immediately by <a href="https://app.jodamoexchange.com/assets-manager/id/assets/user-login">click this</a></p>
<br />
<p>Asset Transfer Request Details</p>
<table width="100%">
<tbody>
<tr><td>Request Document No.</td><td>:</td><td><?php echo $document['docnum']; ?></td></tr>
<tr><td>Transfer Document No.</td><td>:</td><td></td><?php echo $document['docnum_transfer']; ?></td></tr>
<tr><td>Document Date</td><td>:</td><td><?php echo $document['docdate']; ?></td></tr>
<tr><td>Source Location</td><td>:</td><td><?php echo $document['docfrom']; ?></td></tr>
<tr><td>Applicant</td><td>:</td><td><?php echo $document['docapp']; ?></td></tr>
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
