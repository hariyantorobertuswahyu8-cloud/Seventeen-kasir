using System;
using System.Diagnostics;
using System.Drawing;
using System.IO;
using System.Windows.Forms;
using Microsoft.Web.WebView2.Core;
using Microsoft.Web.WebView2.WinForms;

namespace windows_app;

public partial class Form1 : Form
{
    private WebView2 webView = null!;
    private Panel loadingPanel = null!;
    private Label statusLabel = null!;

    public Form1()
    {
        InitializeComponent();
        SetupCustomUI();
    }

    private void SetupCustomUI()
    {
        this.Text = "Seventeen Petshop - Sistem Informasi Kasir POS";
        try
        {
            string iconPath = Path.Combine(AppDomain.CurrentDomain.BaseDirectory, "app.ico");
            if (File.Exists(iconPath))
            {
                this.Icon = new Icon(iconPath);
            }
        }
        catch { }
        this.WindowState = FormWindowState.Maximized;
        this.MinimumSize = new Size(1100, 720);
        this.StartPosition = FormStartPosition.CenterScreen;
        this.BackColor = Color.FromArgb(244, 246, 249);
        this.KeyPreview = true;
        this.KeyDown += Form1_KeyDown;

        // Panel Loading Sementara saat inisialisasi WebView2
        loadingPanel = new Panel
        {
            Dock = DockStyle.Fill,
            BackColor = Color.FromArgb(25, 135, 84)
        };

        statusLabel = new Label
        {
            Text = "Memuat Sistem Kasir Seventeen Petshop...\nMohon tunggu sebentar.",
            ForeColor = Color.White,
            Font = new Font("Segoe UI", 14, FontStyle.Bold),
            TextAlign = ContentAlignment.MiddleCenter,
            Dock = DockStyle.Fill
        };
        loadingPanel.Controls.Add(statusLabel);
        this.Controls.Add(loadingPanel);

        // WebView2 Control
        webView = new WebView2
        {
            Dock = DockStyle.Fill,
            Visible = false
        };
        this.Controls.Add(webView);

        this.Load += async (s, e) =>
        {
            CheckAndStartServices();
            await InitializeWebView();
        };
    }

    private void CheckAndStartServices()
    {
        try
        {
            // Cek Apache
            Process[] httpdProcs = Process.GetProcessesByName("httpd");
            if (httpdProcs.Length == 0 && File.Exists(@"C:\xampp\apache\bin\httpd.exe"))
            {
                ProcessStartInfo psi = new ProcessStartInfo(@"C:\xampp\apache\bin\httpd.exe")
                {
                    WindowStyle = ProcessWindowStyle.Hidden,
                    CreateNoWindow = true,
                    UseShellExecute = true
                };
                Process.Start(psi);
            }

            // Cek MySQL
            Process[] mysqlProcs = Process.GetProcessesByName("mysqld");
            if (mysqlProcs.Length == 0 && File.Exists(@"C:\xampp\mysql\bin\mysqld.exe"))
            {
                ProcessStartInfo psi = new ProcessStartInfo(@"C:\xampp\mysql\bin\mysqld.exe")
                {
                    WindowStyle = ProcessWindowStyle.Hidden,
                    CreateNoWindow = true,
                    UseShellExecute = true
                };
                Process.Start(psi);
            }
        }
        catch (Exception ex)
        {
            Debug.WriteLine("Service check note: " + ex.Message);
        }
    }

    private async Task InitializeWebView()
    {
        try
        {
            // Buat folder user data lokal di AppData agar cache & session tersimpan aman
            string userDataDir = Path.Combine(Environment.GetFolderPath(Environment.SpecialFolder.LocalApplicationData), "SeventeenKasirPOS");
            var environment = await CoreWebView2Environment.CreateAsync(null, userDataDir);
            await webView.EnsureCoreWebView2Async(environment);

            webView.CoreWebView2.Settings.IsStatusBarEnabled = false;
            webView.CoreWebView2.Settings.AreDevToolsEnabled = true;
            webView.CoreWebView2.Settings.IsSwipeNavigationEnabled = false;

            // Event navigasi selesai
            webView.NavigationCompleted += (s, args) =>
            {
                loadingPanel.Visible = false;
                webView.Visible = true;
                webView.Focus();
            };

            // Arahkan ke URL sistem kasir lokal
            webView.Source = new Uri("http://localhost/seventeen_kasir/");
        }
        catch (Exception ex)
        {
            statusLabel.Text = "Terjadi kesalahan saat memuat WebView2:\n" + ex.Message + "\n\nPastikan Apache dan MySQL di XAMPP telah aktif.";
            statusLabel.ForeColor = Color.OrangeRed;
        }
    }

    private void Form1_KeyDown(object? sender, KeyEventArgs e)
    {
        if (e.KeyCode == Keys.F5)
        {
            webView.Reload();
            e.Handled = true;
        }
        else if (e.KeyCode == Keys.F11)
        {
            if (this.FormBorderStyle == FormBorderStyle.None)
            {
                this.FormBorderStyle = FormBorderStyle.Sizable;
                this.WindowState = FormWindowState.Maximized;
            }
            else
            {
                this.FormBorderStyle = FormBorderStyle.None;
                this.WindowState = FormWindowState.Maximized;
            }
            e.Handled = true;
        }
        else if (e.Control && e.KeyCode == Keys.P)
        {
            // Print command
            webView.CoreWebView2?.ExecuteScriptAsync("window.print();");
            e.Handled = true;
        }
        else if (e.KeyCode == Keys.Home && e.Control)
        {
            webView.Source = new Uri("http://localhost/seventeen_kasir/dashboard.php");
            e.Handled = true;
        }
    }

    protected override void OnFormClosing(FormClosingEventArgs e)
    {
        base.OnFormClosing(e);
        webView?.Dispose();
    }
}
