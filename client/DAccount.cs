using System;
using System.Collections;
using System.Collections.Generic;
using System.Security.Cryptography;
using System.Text;
using UnityEngine;
using UnityEngine.Networking;

namespace DiamondAccount
{
    public class DPlayer
    {
        public DPlayer(string[] args)
        {
            int i = 0;
            ID = int.Parse(args[i++]);
            Name = args[i++];
            Username = args[i++];
            LastGame = int.Parse(args[i++]);
            RefPlayerID = int.Parse(args[i++]);
        }

        public int ID { get; private set; }
        public string Name { get; private set; }
        string Username;
        int LastGame;
        int RefPlayerID;
    }

    public class DGame
    {
        public DGame(string[] args)
        {
            int i = 0;
            ID = int.Parse(args[i++]);
            Name = args[i++];
            RequestsCount = int.Parse(args[i++]);
            Version = args[i++];
            Link = args[i++];
            IsMobileGame = args[i++] == "1";
        }
        public DGame()
        {
            ID = -1;
        }

        public int ID { get; private set; }
        public string Name { get; private set; }
        public int RequestsCount { get; private set; }
        public string Version { get; private set; }
        public string Link { get; private set; }
        public bool IsMobileGame { get; private set; }

        public bool IsAvailable => ID != -1;
    }

    public class DFriend
    {
        int ID;
        int From;
        int To;
        bool Accepted;
    }

    public class DAccount : MonoBehaviour
    {
        public delegate void SetupResponse(DGame version);
        public delegate void PlayerResponse(PlayerRes playerres);

        public struct PlayerRes
        {
            public PlayerRes(List<DPlayer> list)
            {
                players = list;
                ErrorCode = -1;
            }

            public PlayerRes(DPlayer player)
            {
                players = new List<DPlayer>((new[] { player }));
                ErrorCode = -1;
            }

            public PlayerRes(int errcode)
            {
                players = new List<DPlayer>();
                ErrorCode = errcode;
            }

            public List<DPlayer> players;
            public DPlayer player => players[0];
            public int ErrorCode;
            public bool isSucces => players.Count > 0;
            public bool isList => players.Count > 1;
        }

        delegate void Response(DReq data);

        struct DReq
        {
            const char DELIMITER = '&';
            const char SUB_DELIMITER = '@';
            public DReq(UnityWebRequest req, Response res)
            {
                request = req;
                callback = new Response(res);
                ErrorCode = -1;
            }

            UnityWebRequest request;
            Response callback;
            public int ErrorCode;
            public string Data => request.downloadHandler.text;
            public bool IsError => request.isNetworkError || request.isHttpError || isAPIError();
            public bool IsFinshed => request.downloadHandler.isDone;
            public void Execute() => callback.Invoke(this);

            bool isAPIError()
            {
                string err = Data;
                return err.Length < 5 && err[0] == 'E' && int.TryParse(err.Substring(1), out ErrorCode);
            }

            public bool ContainMultipleData => isDataContain(DELIMITER);
            public bool ContainSubData => isDataContain(SUB_DELIMITER);

            public string[] GetMainDatas()
            {
                return Data.Split(DELIMITER);
            }

            public string[] GetSubDatas()
            {
                return Data.Split(SUB_DELIMITER);
            }

            bool isDataContain(char c)
            {
                return Data.IndexOf(c) > 0;
            }
        }

        #region Consts

        const string DEFAULT_HOST = "localhost/diamond-account";
        const string API = "/api.php?";
        const string METHOD = "GET";
        const string DEBUG_NAME = "/DA/ ";

        #endregion

        #region Public Propertis

        public bool DebugInfo = true;
        public string Host = DEFAULT_HOST;
        public string ApiKey;

        #endregion

        #region Private Propertis

        private string loginkey;
        private DGame game;
        private bool isAvailable;

        private List<DReq> PendingRequests;

        #endregion

        #region Unity interface

        void Update() => CheckPendings();

        #endregion

        #region Public Inteface

        public void Setup(SetupResponse done)
        {
            //Init Lists
            PendingRequests = new List<DReq>();
            loginkey = "";

            //Start Setup
            SetupResponse r = new SetupResponse(done);
            StartSetup(r);
        }

        public void Login(string username, string password, PlayerResponse playerResponse) 
        {
            if (loginkey != "")
                return;

            PlayerResponse r = new PlayerResponse(playerResponse);
            string[] keys = {"un","ps"};
            string[] vals = {username, CreateMD5(password)};

            SendAPIRequest(BuildURL("login", keys, vals), (res) =>
            {
                if (res.IsError)
                {
                    r.Invoke(new PlayerRes(res.ErrorCode));
                    return;
                }
                loginkey = res.Data;
                ReturnWithPlayerInfos(r);
            });

        }

        public void Register(string username, string ingamename, string password, string email, int reflink, PlayerResponse playerResponse)
        {
            if (loginkey != "")
                return;

            PlayerResponse r = new PlayerResponse(playerResponse);
            string[] keys = { "un", "ig", "ps", "email", "ref" };
            string[] vals = { username, ingamename , password, email, reflink.ToString() };

            SendAPIRequest(BuildURL("register", keys, vals), (res) =>
            {
                if (res.IsError)
                {
                    r.Invoke(new PlayerRes(res.ErrorCode));
                    return;
                }
                loginkey = res.Data;
                ReturnWithPlayerInfos(r);
            });

        }

        #endregion

        #region Private Methods

        void CheckPendings()
        {
            List<DReq> done = new List<DReq>();
            foreach (var req in PendingRequests)
                if (req.IsFinshed)
                    done.Add(req);

            done.ForEach(r => r.Execute());
            done.ForEach(r => PendingRequests.Remove(r));
        }

        void SendAPIRequest(string url, Response res)
        {
            Uri uri = new Uri(url);
            UnityWebRequest request = new UnityWebRequest(uri, METHOD);
            request.downloadHandler = new DownloadHandlerBuffer();
            request.SendWebRequest();
            PendingRequests.Add(new DReq(request, res));

        }

        string BuildURL(string type, string[] keys, string[] vals)
        {
            string baseurl = Host + API + "type=" + type;
            for (int i = 0; i < keys.Length; i++)
                baseurl += "&" + keys[i] + "=" + vals[i];

            //Add apikey
            baseurl += "&apikey=" + ApiKey + ((loginkey != "") ? "&loginkey=" + loginkey : "") ;
             
            return baseurl;
        }

        string BuildURL(string type)
        {
            return Host + API + "type=" + type + "&apikey=" + ApiKey + ((loginkey != "") ? "&loginkey=" + loginkey : "");
        }

        void StartSetup(SetupResponse done)
        {
            SendAPIRequest(BuildURL("api"), (res) =>
            {
                isAvailable = !res.IsError;
                if (isAvailable)
                    SetupGame(done);

                WriteDebug("API is " + (isAvailable ? "available" : "offline"));
            });
        }

        void SetupGame(SetupResponse done)
        {
            SendAPIRequest(BuildURL("game"), (res) =>
            {
                if (!res.IsError)
                {
                    game = new DGame(res.GetMainDatas());
                    WriteDebug("game latest version: " + game.Version);
                }
                else
                    WriteDebug("couldn't get the game datas");

                done.Invoke(game);
            });
        }

        string CreateMD5(string tomd5)
        {
            byte[] asciiBytes = ASCIIEncoding.ASCII.GetBytes(tomd5);
            byte[] hashedBytes = MD5CryptoServiceProvider.Create().ComputeHash(asciiBytes);
            return BitConverter.ToString(hashedBytes).Replace("-", "").ToLower();
        }

        void ReturnWithPlayerInfos(PlayerResponse r)
        {
            SendAPIRequest(BuildURL("info"), (res) =>
            {
                if (res.IsError)
                {
                    r.Invoke(new PlayerRes(res.ErrorCode));
                    return;
                }

                WriteDebug("Palyer " + res.GetMainDatas()[2] + " is loaded");
                r.Invoke(new PlayerRes(new DPlayer(res.GetMainDatas())));
            });
        }

        #endregion

        #region Debug

        void WriteDebug(object msg)
        {
            if(DebugInfo)
                Debug.Log(DEBUG_NAME + msg.ToString());
        }

        #endregion
    }
}
