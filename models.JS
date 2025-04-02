const mongoose = require("mongoose");

const UserSchema = new mongoose.Schema({
  name: String,
  email: { type: String, unique: true },
  password: String,
  role: { type: String, enum: ["student", "employer"], required: true },
  skills: [String], // For students
  company: String, // For employers
});

module.exports = mongoose.model("User", UserSchema);
