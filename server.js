const express = require("express");
const mongoose = require("mongoose");
const cors = require("cors");
require("dotenv").config();

const app = express();
app.use(express.json());
app.use(cors());

// MongoDB Connection
mongoose.connect(process.env.MONGO_URI, {
  useNewUrlParser: true,
  useUnifiedTopology: true,
}).then(() => console.log("Database Connected"))
  .catch(err => console.log(err));

app.get("/", (req, res) => {
  res.send("Job Portal API is running...");
});

app.listen(5000, () => console.log("Server running on port 5000"));
