<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Absen extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('ModelAbsen', 'absen');
		$this->load->model('ModelMahasiswa', 'mahasiswa');
		$this->load->model('ModelAktivitas', 'aktivitas');
		date_default_timezone_set("Asia/Jakarta");
	}
	
	// read table absen untuk user
	public function read($id, $bln = null, $thn=null)
    {    
    	// $id = $this->session->userdata('nip');
		$absen = $this->absen->get_by_id($id);
		// var_dump($absen);
		// die();

		// ngecek ada inputan bulan dan tahun atau ngga, kalo ngga ada nanti lari ke else paling akhir, di mana bulan dan tanggal sesuai dengan yg sekarang
		if ($this->input->post('bulan')){
			$bulan = $this->input->post('bulan');
			$tahun = $this->input->post('tahun');
		}else if ($bln) {
			$bulan = $bln;
			$tahun = $thn;
		}else {
			$bulan = date('m');
			$tahun = date('Y');
		}
		$data = array(
			'title' => 'Data Absen',
			'subtitle' => 'User',
			'bulan' => $bulan,
			'tahun' => $tahun,
			'subtitle2' => 'List Absen Mahasiswa',
			'data' => $absen,
			'page' => 'user/absen/index',
		);
		$this->load->view('templates/app', $data);
    }

	// add aktivitas
    public function add_aktivitas($tgl = null, $bln = null){
    	

        if ($_SESSION['nim'])
			{    	
				$data = $this->aktivitas->addAktivitasUser($tgl);

				 $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-dismissible fade show" role="alert">
		            <strong>Create record success!
		            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		              <span aria-hidden="true">&times;</span>
		            </button>
		            </div>');
				if($tgl){
					// return $this->db->insert('aktivitas', $data);
					redirect(site_url('user/dashboard'));
				}else {
					redirect(site_url('user/dashboard'));
				}
			}else
			{
				$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
		            <strong>Gagal add record!
		            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		              <span aria-hidden="true">&times;</span>
		            </button>
		            </div>');
				if($tgl){
					redirect(site_url('user/dashboard'));
				}else {
					redirect(site_url('user/dashboard'));
				}
		}
    }
    
	// edit aktivitas
    public function edit_aktivitas($id, $sender){
    	 	$ubah = $this->aktivitas->ubahAktivitasUser($id);
			$nim  = $this->session->userdata('nim');
			if ($ubah){
				$this->session->set_flashdata('pesan', '<div class="alert alert-success alert-dismissible fade show" role="alert">
		            <strong>Update record success!
		            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		              <span aria-hidden="true">&times;</span>
		            </button>
		            </div>');
				if($sender == 1){
					redirect(site_url('user/dashboard'));
				}else {
					redirect('user/dashboard');
				}
			}else {
				$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
		            <strong>Gagal update record!
		            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		              <span aria-hidden="true">&times;</span>
		            </button>
		            </div>');
				if($sender == 1){
					redirect(site_url('user/dashboard'));
				}else {
					redirect('user/dashboard');
				}
			}
    }

	// add absen
    public function add_absen($lat = null, $long = null){
    	if ($this->session->userdata('nim')){ // cek ada session dari nim yg login apa ngga

		if ($lat && $long){ // cek ada mapsnya ngga

			$jam = date('H:i'); // ambil jam
			//$jam = date('07:02');

			$absen = $this->absen->getAbsen(); // baca tabl absen

			if($jam > '07:00' && $jam <= '09:00') // validasi jam absen masuk
			{
				if ($absen){
					$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
		            <strong>Anda telah absen!
		            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		              <span aria-hidden="true">&times;</span>
		            </button>
		            </div>');
					redirect('user/dashboard');
				}else{
					$insert = $this->absen->checkinPegawai($lat, $long);
					if ($insert){
						$this->session->set_flashdata('pesan', '<div class="alert alert-success alert-dismissible fade show" role="alert">
			            <strong>Anda berhasil absen!
			            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			              <span aria-hidden="true">&times;</span>
			            </button>
			            </div>');
						redirect('user/dashboard');
					}else{ // maps ngga ada, jam udah lewat, absen ngga bisa dilakukan
						$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
		            <strong>Terjadi kesalah saat absen!
		            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		              <span aria-hidden="true">&times;</span>
		            </button>
		            </div>');
						redirect('user/dashboard');
					}
				}
            	
			}else if ($jam > '15:00' && $jam < '23:59'){ // validasi absen pulang
				if ($absen){
					$update = $this->absen->checkOutPegawai($lat, $long); 

					if ($update){
						$this->session->set_flashdata('pesan', '<div class="alert alert-success alert-dismissible fade show" role="alert">
			            <strong>Berhasil absen pulang!
			            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			              <span aria-hidden="true">&times;</span>
			            </button>
			            </div>');
						redirect('user/dashboard');
					}else{
						// ngga absen masuk, jadi ngga bisa absen
						$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
			            <strong>Mohon maaf, Anda tidak melakukan Absen Masuk!
			            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			              <span aria-hidden="true">&times;</span>
			            </button>
			            </div>');

						redirect('user/dashboard');
					}
				} else {
					// absen berhasil
					$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
			            <strong>Anda telah melakukan Absen Pulang!
			            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			              <span aria-hidden="true">&times;</span>
			            </button>
			            </div>');
					redirect('user/dashboard');
				}

			} else {
				// jam udah lewat, absen ngga bisa dilakukan
				$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
			            <strong>Mohon maaf, Silakan absen dengan waktu yang telah ditentukan!
			            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			              <span aria-hidden="true">&times;</span>
			            </button>
			            </div>');
				redirect('user/dashboard');
			}

		}else {
			// maps ngga ada absen ngga bisa dilakukan
			$this->session->set_flashdata('pesan', '<div class="alert alert-danger alert-dismissible fade show" role="alert">
			            <strong>Mohon maaf, Anda tidak mengizinkan akses lokasi!
			            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			              <span aria-hidden="true">&times;</span>
			            </button>
			            </div>');
			redirect('user/dashboard');
		}

        }
    }

    
    // hapus aktivitas
    public function hapus_aktivitas($id)
	{
		if ($_SESSION['nim']){

			$this->aktivitas->hapusDataAktivitas($id);
			$this->session->set_flashdata('pesan', '<div class="alert alert-success alert-dismissible fade show" role="alert">
			            <strong>Berhasi Dihapus!
			            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
			              <span aria-hidden="true">&times;</span>
			            </button>
			            </div>');
			redirect('user/dashboard');
			
        }
	
	}


}

/* End of file Absen.php */
/* Location: ./application/controllers/user/Absen.php */