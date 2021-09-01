using System;
using System.IO;
using System.Web;
using System.Net;
using System.Linq;
using System.Text;
using Newtonsoft.Json;
using System.Threading.Tasks;
using System.Collections.Generic;
using System.Security.Cryptography;
using System.Collections.Specialized;

namespace E2E_Auth {
    class Handler {
		public static string ENCRYPT_KEY { get; set; }
		public static string URL { get; set; }
		public static string EncryptString(string plainText, byte[] key, byte[] iv) {
			Aes encryptor = Aes.Create();
			encryptor.Mode = CipherMode.CBC;
			encryptor.Key = key;
			encryptor.IV = iv;
			MemoryStream memoryStream = new MemoryStream();
			ICryptoTransform aesEncryptor = encryptor.CreateEncryptor();
			CryptoStream cryptoStream = new CryptoStream(memoryStream, aesEncryptor, CryptoStreamMode.Write);
			byte[] plainBytes = Encoding.ASCII.GetBytes(plainText);
			cryptoStream.Write(plainBytes, 0, plainBytes.Length);
			cryptoStream.FlushFinalBlock();
			byte[] cipherBytes = memoryStream.ToArray();
			memoryStream.Close();
			cryptoStream.Close();
			string cipherText = Convert.ToBase64String(cipherBytes, 0, cipherBytes.Length);
			return cipherText;
		}
		public static string DecryptString(string cipherText, byte[] key, byte[] iv) {
			Aes encryptor = Aes.Create();
			encryptor.Mode = CipherMode.CBC;
			encryptor.Key = key;
			encryptor.IV = iv;
			MemoryStream memoryStream = new MemoryStream();
			ICryptoTransform aesDecryptor = encryptor.CreateDecryptor();
			CryptoStream cryptoStream = new CryptoStream(memoryStream, aesDecryptor, CryptoStreamMode.Write);
			string plainText = String.Empty;
			try {
				byte[] cipherBytes = Convert.FromBase64String(cipherText);
				cryptoStream.Write(cipherBytes, 0, cipherBytes.Length);
				cryptoStream.FlushFinalBlock();
				byte[] plainBytes = memoryStream.ToArray();
				plainText = Encoding.ASCII.GetString(plainBytes, 0, plainBytes.Length);
			}
			finally {
				memoryStream.Close();
				cryptoStream.Close();
			}
			return plainText;
		}
		public static string DECRYPT(string value) {
			SHA256 mySHA256 = SHA256Managed.Create();
			byte[] key = mySHA256.ComputeHash(Encoding.ASCII.GetBytes(Encoding.Default.GetString(Convert.FromBase64String(ENCRYPT_KEY))));
			byte[] iv = new byte[16] { 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F, 0x01 };
			return DecryptString(value, key, iv);
		}
		public static string ENCRYPT(string value) {
			SHA256 mySHA256 = SHA256Managed.Create();
			byte[] key = mySHA256.ComputeHash(Encoding.ASCII.GetBytes(Encoding.Default.GetString(Convert.FromBase64String(ENCRYPT_KEY))));
			byte[] iv = new byte[16] { 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F, 0x01 };
			return EncryptString(value, key, iv);
		}
		public static string RETURN_REQUEST(NameValueCollection Values) {
			try {
				string message = Encoding.Default.GetString(new WebClient { Proxy = null }.UploadValues(URL, Values));
				string password = Encoding.Default.GetString(Convert.FromBase64String(ENCRYPT_KEY));
				SHA256 mySHA256 = SHA256Managed.Create();
				byte[] key = mySHA256.ComputeHash(Encoding.ASCII.GetBytes(password));
				byte[] iv = new byte[16] { 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0A, 0x0B, 0x0C, 0x0D, 0x0E, 0x0F, 0x01 };
				return DecryptString(message, key, iv);
			}
			catch (Exception E) {
				Dictionary<string, string> ERROR = new Dictionary<string, string>();
				ERROR.Add("result", "RETURN_DATA_ERROR fdfsd");
				ERROR.Add("resp", E.Message);
				return JsonConvert.SerializeObject(ERROR);
			}
		}
		public static string LOGIN(string license_key) {
			ENCRYPT_KEY = Convert.ToBase64String(Encoding.Default.GetBytes("ghostisahore"));
			var values = new NameValueCollection();
			values["TYPE"] = "LOGIN";
			values["LICENSE_KEY"] = ENCRYPT(license_key);
			string result = RETURN_REQUEST(values);
			try {
				return result;
			}
			catch {
				return "Unknown Error!";
			}
		}
	}
}
