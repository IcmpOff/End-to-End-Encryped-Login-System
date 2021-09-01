using Newtonsoft.Json;
using System;
using System.Windows.Forms;

namespace E2E_Auth {
    public partial class Main : Form {
        public Main() {
            InitializeComponent();
        }

        private void btnLogin_Click(object sender, EventArgs e) {
			try {
				string LOGIN_RETURN = Handler.LOGIN(txtToken.Text); 
				dynamic LOGIN_RETURN_JSON = JsonConvert.DeserializeObject(LOGIN_RETURN);
				dynamic AUTH = LOGIN_RETURN_JSON.AUTHENTICATION;
				dynamic USER_INFO = LOGIN_RETURN_JSON.USER_INFORMATION;
				switch ((string)AUTH.STATUS) {
					case "SUCCESS":
						Console.WriteLine(string.Format("Hello {0}, {1}{2}", (string)USER_INFO.USERNAME, Environment.NewLine, (string)AUTH.MESSAGE));
						break;
					default:
						Console.WriteLine((string)AUTH.MESSAGE);
						break;
				}
				Console.ReadLine();
			}
			catch {
				Console.WriteLine("Unknown Error");
			}
		}
    }
}
