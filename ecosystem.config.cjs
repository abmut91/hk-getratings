module.exports = {
  apps : [{
    name   : "hk-getratings",
    script : "./index.js",
    env: {
      NODE_ENV: "production",
      PORT: 3000
    }
  }]
}
