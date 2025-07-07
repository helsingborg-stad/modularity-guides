module.exports = {
  roots: ["<rootDir>/source/js"],
  moduleNameMapper: {
    "(.*).js": "$1",
  },
  transform: {
    "^.+\\.tsx?$": "ts-jest",
  },
};
